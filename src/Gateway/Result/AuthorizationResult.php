<?php

Namespace VestiaireCollective\Gateway\Result;

class AuthorizationResult 
{
    public string $result;
    public string $transactionId;
    public array $metadata;

    public function __construct(string $result, string $transactionId, array $metadata) 
    {
        $this->result = $result;
        $this->transactionId = $transactionId;
        $this->metadata = $metadata;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}