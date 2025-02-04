<?php

namespace VestiaireCollective\Factory;

use VestiaireCollective\Gateway\AuthorizationGatewayInterface;
use VestiaireCollective\Gateway\CaptureGatewayInterface;
use VestiaireCollective\Gateway\RefundGatewayInterface;

interface ProviderGatewayFactoryInterface 
{
    public function createCaptureProvider(string $provider): ?CaptureGatewayInterface;

    public function createRefundProvider(string $provider): ?RefundGatewayInterface;

    public function createAuthorizationProvider(string $provider): ?AuthorizationGatewayInterface;
}
