<?php

namespace VestiaireCollective\Entity\Builder;

use VestiaireCollective\Entity\Capture;
class CaptureBuilder
{
    private int $id;
    private string $publicId;
    private string $result;
    private string $provider;
    private string $authorizationId;

    public static function builder(): CaptureBuilder 
    {
        return new CaptureBuilder();
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

    public function withAuthorizationId(string $authorizationId): self
    {
        $this->authorizationId = $authorizationId;
        return $this;
    }

    public function build(): Capture 
    {
        $capture = new Capture($this->result, $this->provider, $this->authorizationId); 

        if (isset($this->id)) {
            $capture->setId($this->id);
        }

        if (isset($this->publicId)) {
            $capture->setPublicId($this->publicId);
        }

        return $capture;       
    }
}