<?php

namespace VestiaireCollective\UseCase;

use VestiaireCollective\Repository\AuthorizationRepositoryInterface;
use VestiaireCollective\Repository\CaptureRepositoryInterface;
use VestiaireCollective\Factory\ProviderGatewayFactoryInterface;
use VestiaireCollective\Gateway\Request\CaptureRequest;
use VestiaireCollective\UseCase\UseCaseAbstract;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\Entity\Builder\CaptureBuilder;
use VestiaireCollective\Exception\UnknownException;
use VestiaireCollective\UseCase\Validator\AmountValidator;
use VestiaireCollective\UseCase\Validator\TransactionIdValidator;
use VestiaireCollective\Exception\EntityNotFoundException;

class CaptureUseCase extends UseCaseAbstract
{
    private AuthorizationRepositoryInterface $authorizationRepository;

    private CaptureRepositoryInterface $captureRepository;

    private ProviderGatewayFactoryInterface $providerGatewayFactory;

    public function __construct(
        ProviderGatewayFactoryInterface $providerGatewayFactory,
        AuthorizationRepositoryInterface $authorizationRepository,
        CaptureRepositoryInterface $captureRepository
    )
    {
        $this->providerGatewayFactory = $providerGatewayFactory;
        $this->captureRepository = $captureRepository;
        $this->authorizationRepository = $authorizationRepository;
    }

    public function getRequiredFieldAndValidator(): array
    {
        return [
            "auth_token" => TransactionIdValidator::class,
            "amount" => AmountValidator::class,
        ];
    }

    public function execute(UseCaseRequest $request): UseCaseResponse 
    {
        $authorization = $this->authorizationRepository->findByPublicId($request->getRequest()['auth_token']);

        $this->checkCaptureAsAlreadyBeenDone($authorization->getPublicId());
        
        $gateway = $this->providerGatewayFactory->createCaptureProvider($authorization->getProvider());
        
        $providerResult = $gateway->capture(new CaptureRequest());
        
        $capture = CaptureBuilder::builder()
            ->withResult($providerResult->getResult())
            ->withProvider($authorization->getProvider())
            ->withAuthorizationId($authorization->getPublicId())
            ->build();

        $result = $this->captureRepository->save($capture);                

        if ($result === false) {
            throw new UnknownException('Unexpected error');
        }

        if ($providerResult->getResult() === 'success') {
            return new UseCaseResponse(['status' => 'success', 'transaction_id' => $capture->getPublicId()]); 
        } else {
            return new UseCaseResponse(['status' => 'failed', 'transaction_id' => $capture->getPublicId()]);
        }
    }

    private function checkCaptureAsAlreadyBeenDone(string $authorizationId): void
    {
        try {
            $capture = $this->captureRepository->findByAuthorizationId($authorizationId);
            if ($capture !== null) {
                throw new UnknownException('Already existing capture');
            }
        } catch (EntityNotFoundException $e) {
            #do nothing 
            return ;
        }
    }
}
