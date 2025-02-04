<?php

namespace VestiaireCollective\Gateway;

use  VestiaireCollective\Gateway\Request\AuthorizationRequest;
use VestiaireCollective\Gateway\Result\AuthorizationResult;
use VestiaireCollective\Gateway\Request\CaptureRequest;
use VestiaireCollective\Gateway\Result\CaptureResult;
use VestiaireCollective\Gateway\Request\RefundRequest;
use VestiaireCollective\Gateway\Result\RefundResult;
use VestiaireCollective\Gateway\AuthorizationGatewayInterface;
use VestiaireCollective\Gateway\CaptureGatewayInterface;
use VestiaireCollective\Gateway\RefundGatewayInterface;

class ProviderBGateway implements AuthorizationGatewayInterface, CaptureGatewayInterface, RefundGatewayInterface
{
    public function authorize(AuthorizationRequest $request): AuthorizationResult 
    {
        return new AuthorizationResult('success', 'trx_providerB', []);
    }

    public function capture(CaptureRequest $request): CaptureResult
    {
        return new CaptureResult('success', 'capture_providerB' . uniqid());
    }

    public function refund(RefundRequest $request): RefundResult
    {
        return new RefundResult('success', 'refund_providerB' . uniqid());
    }

}