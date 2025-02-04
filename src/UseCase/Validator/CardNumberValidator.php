<?php

namespace VestiaireCollective\UseCase\Validator;

use VestiaireCollective\Exception\InvalidFieldException;

class CardNumberValidator
{
    public function validate(string $cardNumber): bool
    {
        if (!is_string($cardNumber) || !preg_match('/^\d{16}$/', $cardNumber)) {
            throw new InvalidFieldException("Card number must be 16 digits.");
        }
        
        return true;
    }
}
