<?php

namespace VestiaireCollective\UseCase;

use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\Exception\MissingFieldException;

Abstract class UseCaseAbstract
{
    abstract public function execute(UseCaseRequest $request): UseCaseResponse;

    abstract public function getRequiredFieldAndValidator(): array;

    public function isRequestValid(UseCaseRequest $request): void  
    {
        $requestArray = $request->getRequest();
        $requiredField = $this->getRequiredFieldAndValidator();

        foreach ($requiredField as $field => $validatorName) {
            if (array_key_exists($field, $requestArray) === true) {
                $validator = new $validatorName();
                $validator->validate($requestArray[$field]);
            } else {
                throw new MissingFieldException("Field " . $field . " missing");
            }
        }
    }
}
