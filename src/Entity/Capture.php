<?php

namespace VestiaireCollective\Entity;

class Capture 
{
    private int $id;
    private string $publicId;
    private string $result;
    private string $provider;
    private string $authorizationId;
    
    public function __construct(string $result, string $provider, string $authorizationId)
    {
        $this->result = $result;
        $this->provider = $provider;
        $this->authorizationId = $authorizationId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId): void
    {
        $this->publicId = $publicId;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getAuthorizationId(): string
    {
        return $this->authorizationId;
    }
}