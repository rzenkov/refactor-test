<?php

namespace App\DTO\PurchaseEvent;

use App\Models\PixelLog;

class PixelDTO
{

    private $id;
    private $ppId;
    private $clickId;
    private $utmTerm;
    private $createdAt;

    public function __construct($id, $ppId, $clickId, $utmTerm, $createdAt)
    {
        $this->id =  $id;
        $this->ppId = $ppId;
        $this->clickId = $clickId;
        $this->utmTerm = $utmTerm;
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getPpId()
    {
        return $this->ppId;
    }
    public function getClickId()
    {
        return $this->clickId;
    }
    public function getUtmTerm()
    {
        return $this->utmTerm;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public static function fromPixelLog(PixelLog $log)
    {
        return new self(
            $log->id,
            $log->ppId,
            $log->data['click_id'] ?? null,
            $log->data['utm_term'] ?? null,
            $log->created_at
        );
    }
}
