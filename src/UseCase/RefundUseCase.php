<?php

namespace VestiaireCollective\UseCase;

use VestiaireCollective\Repository\CaptureRepositoryInterface;
use VestiaireCollective\Factory\ProviderGatewayFactoryInterface;
use VestiaireCollective\Gateway\Request\RefundRequest;
use VestiaireCollective\UseCase\UseCaseAbstract;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\Entity\Builder\RefundBuilder;
use VestiaireCollective\Exception\UnknownException;
use VestiaireCollective\Repository\RefundRepositoryInterface;
use VestiaireCollective\UseCase\Validator\AmountValidator;
use VestiaireCollective\UseCase\Validator\TransactionIdValidator;
use VestiaireCollective\Exception\EntityNotFoundException;

class RefundUseCase extends UseCaseAbstract
{
    private CaptureRepositoryInterface $captureRepository;

    private ProviderGatewayFactoryInterface $providerGatewayFactory;

    private RefundRepositoryInterface $refundRepository;
    public function __construct(
        ProviderGatewayFactoryInterface $providerGatewayFactory,
        CaptureRepositoryInterface $captureRepository,
        RefundRepositoryInterface $refundRepository
    )
    {
        $this->providerGatewayFactory = $providerGatewayFactory;
        $this->captureRepository = $captureRepository;
        $this->refundRepository = $refundRepository;
    }

    public function getRequiredFieldAndValidator(): array
    {
        return [
            "amount" => AmountValidator::class,
            "transaction_id" => TransactionIdValidator::class,
        ];
    }

    public function execute(UseCaseRequest $request): UseCaseResponse 
    {
        $capture = $this->captureRepository->findByPublicId($request->getRequest()['transaction_id']);

        $this->checkIfRefundHasAlreadyBeenDone($capture->getPublicId());

        $gateway = $this->providerGatewayFactory->createRefundProvider($capture->getProvider());
        
        $providerResult = $gateway->refund(new RefundRequest());

        if ($providerResult->getResult() === 'success')
        {
            $refund = RefundBuilder::builder()
                ->withResult($providerResult->getResult())
                ->withProvider($capture->getProvider())
                ->withCaptureId($capture->getPublicId())
                ->build();

            $result = $this->refundRepository->save($refund);                

            if ($result === false) {
                throw new UnknownException('Unexpected error');
            }
        } else {
            return new UseCaseResponse(['refund' => 'error when processing refund']);
        }

        return new UseCaseResponse(['refund' => 'success', 'transaction_id' => $refund->getPublicId()]); 
    }

    private function checkIfRefundHasAlreadyBeenDone(string $captureId): void
    {
        try {
            $refund = $this->refundRepository->findByCaptureId($captureId);
            if ($refund !== null) {
                throw new UnknownException('Already existing refund');
            }
        } catch (EntityNotFoundException $e) {
            #do nothing 
            return ;
        }
    }
}
