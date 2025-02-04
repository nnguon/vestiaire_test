<?php

namespace VestiaireCollective\UseCase\Validator;

use VestiaireCollective\Exception\InvalidFieldException;

class ExpiryDateValidator
{
    public function validate(string $value): bool
    {
        if (!is_string($value) || !preg_match('/^\d{2}\/\d{2}$/', $value)) {
            throw new InvalidFieldException("Expiry date must be in MM/YY format.");
        }

        return true;
    }
}
