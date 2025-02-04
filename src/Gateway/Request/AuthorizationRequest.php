<?php

Namespace VestiaireCollective\Gateway\Request;

class AuthorizationRequest {
    public string $amount;
    public string $cardNumber;
    public string $expiryDate;
    public string $cvv;

    /**
     * AuthorizationRequest constructor.
     *
     * @param string $amount
     * @param string $cardNumber
     * @param string $expiryDate (e.g., "MM/YY")
     * @param string $cvv
     */
    public function __construct(float $amount, string $cardNumber, string $expiryDate, string $cvv) {
        $this->amount = $amount;
        $this->cardNumber = $cardNumber;
        $this->expiryDate = $expiryDate;
        $this->cvv = $cvv;
    }
}