<?php

namespace App\DTO\PurchaseEvent;

class ProductDTO
{
    private $id;
    private $name;
    private $category;
    private $price;
    private $quantity;
    public function __construct($id, $name, $category, $price, $quantity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->category = $category;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public static function fromArray(array $productData)
    {
        return new self(
            $productData['id'],
            trim(($productData['name'] ?? '') . ' ' . ($productData['variant'] ?? '')),
            $productData['category'] ?? null,
            $productData['price'],
            $productData['quantity'] ?? 1
        );
    }
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getCategory()
    {
        return $this->category;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }
}
