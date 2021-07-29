<?php

namespace App\Processors;

use Illuminate\Log\Logger;
use App\DTO\PurchaseEvent\PurchaseEventDTO;
use App\Models\Order;
use App\Models\OrdersProduct;


class CreateOrUpdateOrderAction
{
    /**
     * этой константе тут не место, но вынес её для того чтобы обратить внимание
     */
    private const NEW_ORDER_STATUS = 'new';

    private $orderQuery;
    private $productsQuery;
    private $logger;

    public function __construct(Order $orderQuery, OrdersProduct $productsQuery, Logger $logger)
    {
        $this->orderQuery = $orderQuery;
        $this->productsQuery = $productsQuery;
        $this->logger = $logger;
    }

    private function findOrCreateProduct($pixelPpId, $orderId, $productId): OrdersProduct
    {

        $product = $this->productsQuery
            ->where('pp_id', '=', $pixelPpId)
            ->where('order_id', '=', $orderId)
            ->where('product_id', '=', $productId)
            ->first() ?? new OrdersProduct();

        return $product;
    }
    private function findOrCreateOrder($pixelPpId, $orderId): Order
    {

        $order = $this->orderQuery
            ->where('pp_id', '=', $pixelPpId)
            ->where('order_id', '=', $orderId)
            ->first();

        if (!$order) {
            $this->logger->debug('Заказ №' . $orderId . ' не существует, создаем');
            $order = new Order();
            $order->pp_id = $pixelPpId;
            $order->order_id = $orderId;
            $order->status = self::NEW_ORDER_STATUS;
        } else {
            $this->logger->debug('Заказ №' . $orderId . ' существует, обновляем');
        }

        return $order;
    }

    private function createOrderFor(PurchaseEventDTO $event): Order
    {
        $order = $this->findOrCreateOrder(
            $event->getPixel()->getPpId(),
            $event->getOrder()->getId()
        );

        $order->pixel_id = $event->getPixel()->getId();
        $order->datetime = $event->getPixel()->getCreatedAt();
        $order->click_id = $event->getPixel()->getClickId();
        $order->web_id = $event->getPixel()->getUtmTerm();

        $order->partner_id = $event->getLink()->getPartnerId();
        $order->link_id = $event->getLink()->getId();
        $order->offer_id = $event->getLink()->getOfferId();

        $order->client_id = $event->getClientId();

        $order->gross_amount = $event->getGrossAmount();
        $order->cnt_products = $event->getProductsCount();

        $order->save();
        return $order;
    }

    private function createProductsFor(Order $order, PurchaseEventDTO $event): void
    {
        foreach ($event->getProducts() as $productDTO) {
            $product = $this->findOrCreateProduct(
                $event->getPixel()->getPpId(),
                $event->getOrder()->getId(),
                $productDTO->getId(),
            );

            //  TODO - Нужно реализовать в модели метод fillFrom и передавать данные в модель 
            $product->pp_id = $this->pixel_log->pp_id;
            // From Order
            $product->order_id = $order->order_id;
            $product->parent_id = $order->id;
            $product->datetime = $order->datetime;
            $product->partner_id = $order->partner_id;
            $product->offer_id = $order->offer_id;
            $product->link_id = $order->link_id;
            $product->web_id = $order->web_id;
            $product->click_id = $order->click_id;
            $product->pixel_id = $order->pixel_id;

            // From productDTO
            $product->product_id = $productDTO->getId();
            $product->product_name = $productDTO->getName();
            $product->category = $productDTO->getCategory();
            $product->price = $productDTO->getPrice();
            $product->quantity = $productDTO->getQuantity() ?? 1;

            // Calculating - нужно переность такую математику в модель
            $product->total = $product->price * $product->quantity;
            // Default values
            $product->amount = 0;
            $product->amount_advert = 0;
            $product->fee_advert = 0;
            $product->save();

            $this->logger->debug('Сохранен продукт: ' . $product->product_name);
        }
    }

    public function handle(PurchaseEventDTO $event): void
    {
        $order = $this->createOrderFor($event);

        $this->logger->debug('Найдено продуктов: ' . $event->getProductsCount());

        $this->createProductsFor($order, $event);
    }
}
