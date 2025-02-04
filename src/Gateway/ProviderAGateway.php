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

class ProviderAGateway implements AuthorizationGatewayInterface, CaptureGatewayInterface, RefundGatewayInterface
{
    public function authorize(AuthorizationRequest $request): AuthorizationResult 
    {
        if ($request->cardNumber[0] === '5') {
            return new AuthorizationResult('failure', 'trx_providerA' . uniqid(), []);
        }

        return new AuthorizationResult('success', 'trx_providerA' . uniqid(), []);
    }

    public function capture(CaptureRequest $request): CaptureResult
    {
        return new CaptureResult('success', 'capture_providerA' . uniqid());
    }

    public function refund(RefundRequest $request): RefundResult
    {
        return new RefundResult('success', 'refund_providerA' . uniqid());
    }
}