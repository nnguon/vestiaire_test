<?php

namespace VestiaireCollective\UseCase\Validator;

use VestiaireCollective\Exception\InvalidFieldException;

class AmountValidator
{
    public function validate(string $value): bool
    {
        if (!is_int($value) && !ctype_digit(strval($value))) {
            throw new InvalidFieldException("Amount must be an integer.");
        }

        if ($value <= 0) {
            throw new InvalidFieldException("Amount must be greater than zero.");
        }
        return true;
    }
}
