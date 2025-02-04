<?php

namespace VestiaireCollective\UseCase\Request;

class UseCaseRequest
{
    private array $request;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    public function getRequest(): array
    {
        return $this->request;
    }
}