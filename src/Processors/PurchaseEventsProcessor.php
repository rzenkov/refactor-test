<?php

namespace App\Processors;

use Illuminate\Log\Logger;
use App\DTO\PurchaseEvent\LinkDTO;
use App\DTO\PurchaseEvent\OrderDTO;
use App\DTO\PurchaseEvent\PixelDTO;
use App\DTO\PurchaseEvent\ProductDTO;
use App\DTO\PurchaseEvent\PurchaseEventDTO;
use App\Models\Link;
use App\Models\PixelLog;

class PixelLogEventsProcessor
{
    private $action;
    private $logger;

    public function __construct(CreateOrUpdateOrderAction $action, Logger $logger)
    {
        $this->action = $action;
        $this->logger = $logger;
    }


    public function process($clientId, Link $link, PixelLog $pixelLog)
    {

        $event = $this->getPurchaseEvent($pixelLog);
        if (is_null($event)) {
            return;
        }

        $this->ensureEventIsValid($event);

        $eventDTO = PurchaseEventDTO::makeFrom(
            $clientId,
            OrderDTO::fromArray($event),
            PixelDTO::fromPixelLog($pixelLog),
            LinkDTO::fromLink($link),
            array_map(function ($productData) {
                return ProductDTO::fromArray($productData);
            }, $event['products']),
        );

        $this->action->handle($eventDTO);
        $pixelLog->is_order = true; // TODO заменить на метод в модели
        return true;
    }

    private function ensureEventIsValid(array $purchaseEvent)
    {
        $validator = Validator::make($purchaseEvent, [
            'products.*.id' => 'required|string',
            'products.*.name' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.variant' => 'nullable|string',
            'products.*.category' => 'nullable|string',
            'products.*.quantity' => 'nullable|numeric|min:1',
            'actionField.id' => 'required|string',
            'actionField.action' => 'nullable|string|in:purchase',
            'actionField.revenue' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $this->logger->debug('Ошибка валидации заказа');
            throw new ValidationException($validator);
        }
    }

    // TODO - данный метод должен быть перемещен в модель PixelLog
    private function getPurchaseEvent(PixelLog $pixelLog): array
    {
        if (!is_array($pixelLog->data['dataLayer'])) {
            throw new \Exception('dataLayer is not an array');
        }

        $events = collect($pixelLog->data['dataLayer'])
            ->filter(function ($event) {
                return isset($event['event']);
            })
            ->filter(function ($event) {
                return isset($event['ecommerce']) &&
                    isset($event['ecommerce']['purchase']);
            });

        return $events->first();
    }
}
