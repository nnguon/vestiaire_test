<?php

namespace VestiaireCollective\UseCase;

use VestiaireCollective\Configuration\Configuration;
use Exception;
use VestiaireCollective\Factory\ProviderGatewayFactoryInterface;
use VestiaireCollective\Gateway\Request\AuthorizationRequest;
use VestiaireCollective\Repository\AuthorizationRepositoryInterface;
use VestiaireCollective\Entity\Builder\AuthorizationBuilder;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\Exception\UnknownException;
use VestiaireCollective\UseCase\Validator\AmountValidator;
use VestiaireCollective\UseCase\Validator\CardNumberValidator;
use VestiaireCollective\UseCase\Validator\ExpiryDateValidator;
use VestiaireCollective\UseCase\Validator\CvvValidator;

class AuthorizationUseCase extends UseCaseAbstract
{
    private Configuration $config;

    private ProviderGatewayFactoryInterface $providerGatewayFactory;

    private AuthorizationRepositoryInterface $authorizationRepository;

    public function __construct(
        Configuration $config,
        ProviderGatewayFactoryInterface $providerGatewayFactory,
        AuthorizationRepositoryInterface $authorizationRepository
        )
    {
        $this->config = $config;
        $this->providerGatewayFactory = $providerGatewayFactory;
        $this->authorizationRepository = $authorizationRepository;
    }

    public function getRequiredFieldAndValidator(): array
    {
        return [
            "amount" => AmountValidator::class,
            "card_number" => CardNumberValidator::class,
            "expiry_date" => ExpiryDateValidator::class,
            "cvv" => CvvValidator::class
        ];
    }

    #method to handle balancing should probably be in another class
    private function choosePaymentProvider(): string
    {
        $data = $this->config->get('providers_balancing');
        $currentWeight = 0;

        if (empty($data) === true) {
            #echo("Providers_balancing confing not found or empty");
            throw new Exception(message: "Could not select a provider");
        }

        $rand = mt_rand(1, array_sum($data)); 
        foreach ($data as $provider => $weight) {
            $currentWeight += $weight;
            if ($rand <= $currentWeight) {
                return $provider;
            }
        }

        throw new Exception(message: "Could not select a provider");
    }

    public function execute(UseCaseRequest $useCaseRequest): UseCaseResponse 
    {
        $request = $useCaseRequest->getRequest();

        $selectedProvider = $this->choosePaymentProvider();

        #echo("calling provider" . $selectedProvider);
        $providerGateway = $this->providerGatewayFactory->createAuthorizationProvider($selectedProvider);

        #should use a builder to create Request
        $providerResult = $providerGateway->authorize(new AuthorizationRequest($request['amount'], $request['card_number'], $request['expiry_date'], $request['cvv']));

        $authorization = AuthorizationBuilder::builder()
            ->withAmount($request['amount'])
            ->withCardNumber($request['card_number'])
            ->withExpiryDate($request['expiry_date'])
            ->withCvv($request['cvv'])
            ->withResult($providerResult->getResult())
            ->withProvider($selectedProvider)
            ->build();

        $result = $this->authorizationRepository->save($authorization);

        if ($result === false) {
            throw new UnknownException('Unexpected error');
        }

        if ($providerResult->getResult() === 'success') {
            return new UseCaseResponse(['status' => 'success', 'auth_token' => $authorization->getPublicId()]);
        } else {
            return new UseCaseResponse(['status' => 'failed', 'auth_token' => $authorization->getPublicId()]);
        }
    }
}
