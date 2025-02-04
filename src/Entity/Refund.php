<?php

namespace VestiaireCollective\Entity;

class Refund 
{
    private int $id;
    private string $publicId;
    private string $result;
    private string $provider;
    private string $captureId;

    public function __construct(string $result, string $provider, string $captureId)
    {
        $this->result = $result;
        $this->provider = $provider;
        $this->captureId = $captureId;
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

    public function getCaptureId(): string
    {
        return $this->captureId;
    }
}