<?php

namespace App\DTO\PurchaseEvent;

class OrderDTO
{
    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function fromArray(array $array)
    {
        return new self($array['actionField']['id']);
    }

    public function getId()
    {
        return $this->id;
    }
}
