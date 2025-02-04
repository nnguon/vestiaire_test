<?php

namespace VestiaireCollective\UseCase\Validator;

use VestiaireCollective\Exception\InvalidFieldException;

class TransactionIdValidator
{
    public function validate(string $value): bool
    {
        return true;
    }
}
