<?php

namespace App\DTO\PurchaseEvent;

use App\Models\PixelLog;

class PurchaseEventDTO
{
    private $clientId;
    private $pixel;
    private $order;
    private $link;
    private array $products;

    public function __construct($clientId,  OrderDTO $order, PixelDTO $pixel, LinkDTO $link, array $products)
    {
        $this->clientId = $clientId;
        $this->pixel = $pixel;
        $this->order = $order;
        $this->link = $link;
        $this->products = $products;
    }
    public static function makeFrom($clientId, OrderDTO $order, PixelDTO $pixel,  LinkDTO $link, array $products)
    {
        return new self(
            $clientId,
            $order,
            $pixel,
            $link,
            $products
        );
    }
    public function getOrder(): OrderDTO
    {
        return $this->order;
    }

    public function getPixel(): PixelDTO
    {
        return $this->pixel;
    }
    /**
     * @return ProductDTO[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }
    public function getProductsCount(): int
    {
        return count($this->products);
    }
    public function getGrossAmount()
    {
        return array_reduce($this->prodcuts, function ($gross, ProductDTO $product) {
            $amount = $product->getPrice() * ($product->getQuantity() ?? 1);
            return $gross += $amount;
        }, 0);
    }
    public function getLink(): LinkDTO
    {
        return $this->link;
    }
    public function getClientId()
    {
        return $this->clientId;
    }
}
