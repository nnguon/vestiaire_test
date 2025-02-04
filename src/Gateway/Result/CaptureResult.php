<?php

Namespace VestiaireCollective\Gateway\Result;

class CaptureResult 
{
    public string $result;
    public string $transactionId;

    public function __construct(string $result, string $transactionId) 
    {
        $this->result = $result;
        $this->transactionId = $transactionId;
    }

    public function getResult(): string
    {
        return $this->result;
    }

}