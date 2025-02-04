<?php

namespace VestiaireCollective\Entity;

class Authorization 
{
    private int $id;
    private string $publicId;
    private int $amount;
    private string $expiryDate;
    private string $cvv;
    private string $cardNumber;
    private string $result;
    private string $provider;

    public function __construct(int $amount, string $expiryDate, string $cvv, string $cardNumber, string $result, string $provider)
    {
        $this->amount = $amount;
        $this->expiryDate = $expiryDate;
        $this->cvv = $cvv;
        $this->cardNumber = $cardNumber;
        $this->result = $result;
        $this->provider = $provider;
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getExpiryDate(): string
    {
        return $this->expiryDate;
    }

    public function getCreditCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getCvv(): string
    {
        return $this->cvv;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}