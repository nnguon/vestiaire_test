<?php

namespace VestiaireCollective\Entity\Builder;

use VestiaireCollective\Entity\Authorization;
class AuthorizationBuilder
{
    private int $id;
    private string $publicId;
    private int $amount;
    private string $expiryDate;
    private string $cvv;
    private string $cardNumber;
    private string $result;
    private string $provider;
    public static function builder(): AuthorizationBuilder 
    {
        return new AuthorizationBuilder();
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

    public function withAmount(int $amount): self 
    {
        $this->amount = $amount;
        return $this;
    }

    public function withExpiryDate(string $expiryDate): self 
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    public function withCardNumber(string $cardNumber): self 
    {
        $this->cardNumber = $cardNumber;
        return $this;
    }

    public function withCvv(string $cvv): self 
    {
        $this->cvv = $cvv;
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

    public function build(): Authorization 
    {
        $authorization = new Authorization($this->amount, $this->expiryDate, $this->cvv, $this->cardNumber, $this->result, $this->provider); 

        if (isset($this->id)) {
            $authorization->setId($this->id);
        }

        if (isset($this->publicId)) {
            $authorization->setPublicId($this->publicId);
        }

        return $authorization;       
    }
}