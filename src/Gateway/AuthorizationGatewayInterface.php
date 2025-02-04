<?php

namespace VestiaireCollective\Gateway;

use  VestiaireCollective\Gateway\Request\AuthorizationRequest;
use VestiaireCollective\Gateway\Result\AuthorizationResult;
interface AuthorizationGatewayInterface 
{
    public function authorize(AuthorizationRequest $authorizationRequest): AuthorizationResult;
}
