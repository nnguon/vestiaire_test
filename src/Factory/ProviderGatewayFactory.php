<?php

namespace VestiaireCollective\Factory;

use VestiaireCollective\Gateway\ProviderAGateway;
use VestiaireCollective\Gateway\ProviderBGateway;
use VestiaireCollective\Gateway\AuthorizationGatewayInterface;
use VestiaireCollective\Gateway\CaptureGatewayInterface;
use VestiaireCollective\Gateway\RefundGatewayInterface;

class ProviderGatewayFactory implements ProviderGatewayFactoryInterface
{
    private function create(string $provider)
    {
        switch ($provider) {
            case "ProviderA":
                return new ProviderAGateway();
            case "ProviderB":
                return new ProviderBGateway();
            default:
                return null;

        }
    }

    public function createCaptureProvider(string $provider): ?CaptureGatewayInterface
    {
        return $this->create($provider);
    }

    public function createRefundProvider(string $provider): ?RefundGatewayInterface
    {
        return $this->create($provider);
    }

    public function createAuthorizationProvider(string $provider): ?AuthorizationGatewayInterface
    {
        return $this->create($provider);
    }

}
