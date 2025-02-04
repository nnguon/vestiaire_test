<?php

namespace VestiaireCollective\UseCase\Validator;

use VestiaireCollective\Exception\InvalidFieldException;

class CvvValidator
{
    public function validate(string $value): bool
    {
        if (!is_string($value) || !preg_match('/^\d{3}$/', $value)) {
            throw new InvalidFieldException("CVV must be 3 digits.");
        }
        
        return true;
    }
}
