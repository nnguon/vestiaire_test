<?php

namespace VestiaireCollective\Entity\Builder;

use VestiaireCollective\Entity\Refund;
class RefundBuilder
{
    private int $id;
    private string $publicId;
    private string $result;
    private string $provider;
    private string $captureId;

    public static function builder(): RefundBuilder 
    {
        return new RefundBuilder();
    }

    public function withId(int $id): self 
    {
        $this->id = $id;
        return $this;
    }

    public function withPublicId(string $publicId): self 
    {
        $this->publicId = $publicId;
        return $this;
    }

    public function withResult(string $result): self 
    {
        $this->result = $result;
        return $this;
    }

    public function withProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function withCaptureId(string $captureId): self
    {
        $this->captureId = $captureId;
        return $this;
    }

    public function build(): Refund 
    {
        $refund = new Refund($this->result, $this->provider, $this->captureId); 

        if (isset($this->id)) {
            $refund->setId($this->id);
        }

        if (isset($this->publicId)) {
            $refund->setPublicId($this->publicId);
        }

        return $refund;       
    }
}