<?php

namespace App\DTO\PurchaseEvent;

use App\Models\Link;

class LinkDTO
{
    private $id;
    private $partnerId;
    private $offerId;
    public function __construct($id, $partnerId, $offerId)
    {
        $this->id = $id;
        $this->partnerId = $partnerId;
        $this->offerId = $offerId;
    }
    public static function fromLink(Link $link)
    {
        return new self(
            $link->id,
            $link->partner_id,
            $link->offer_id,
        );
    }

    public function getId()
    {
        return $this->id;
    }
    public function getPartnerId()
    {
        return $this->partnerId;
    }
    public function getOfferId()
    {
        return $this->offerId;
    }
}
