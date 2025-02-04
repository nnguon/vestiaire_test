<?php

namespace VestiaireCollective\Tests\Unit\UseCase;
use PHPUnit\Framework\TestCase;
use VestiaireCollective\UseCase\RefundUseCase;
use VestiaireCollective\Repository\RefundRepositoryInterface;
use VestiaireCollective\Repository\CaptureRepositoryInterface;
use VestiaireCollective\Factory\ProviderGatewayFactoryInterface;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use PHPUnit\Framework\MockObject\MockObject;
use VestiaireCollective\Exception\EntityNotFoundException;
use VestiaireCollective\Gateway\RefundGatewayInterface;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\Entity\Builder\CaptureBuilder;
use VestiaireCollective\Exception\UnknownException;
use VestiaireCollective\Gateway\Result\RefundResult;
use VestiaireCollective\Entity\Builder\RefundBuilder;
use VestiaireCollective\UseCase\Validator\AmountValidator;
use VestiaireCollective\UseCase\Validator\TransactionIdValidator;

class RefundUseCaseTest extends TestCase
{
    private RefundUseCase $useCase;
    /** @var CaptureRepositoryInterface|MockObject $captureRepository */ 
    private CaptureRepositoryInterface $captureRepository;

    /** @var RefundRepositoryInterface|MockObject $refundRepository */ 
    private RefundRepositoryInterface $refundRepository;

    /** @var ProviderGatewayFactoryInterface|MockObject $providerGatewayFactory */ 
    private ProviderGatewayFactoryInterface $providerGatewayFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->captureRepository = $this->createMock(CaptureRepositoryInterface::class);
        $this->refundRepository = $this->createMock(RefundRepositoryInterface::class);
        $this->providerGatewayFactory = $this->createMock(ProviderGatewayFactoryInterface::class);

        $this->useCase = new RefundUseCase(
            $this->providerGatewayFactory,
            $this->captureRepository,
            $this->refundRepository
        );
    }

    public function testExecuteSuccess()
    {
        $requestData = ['amount' => 100, 'transaction_id' => 'cap_123'];
        $request = new UseCaseRequest($requestData);

        $capture = CaptureBuilder::builder()
            ->withResult('success')
            ->withProvider('providerA')
            ->withAuthorizationId('auth_123')
            ->withPublicId('cap_123')
            ->build();

        $this->captureRepository->method('findByPublicId')->willReturn($capture);
        $this->refundRepository->method('findByCaptureId')->willThrowException(new EntityNotFoundException());

        $refundProvider = $this->createMock(RefundGatewayInterface::class);
        $providerResult = new RefundResult('success', transactionId: 'ref_123');;
        $refundProvider->method('refund')->willReturn($providerResult);
        $this->providerGatewayFactory->method('createRefundProvider')->willReturn($refundProvider);

        #use callback method to set publicId as it is set in repository
        $this->refundRepository->method('save')->willReturnCallback(function ($passedEntity): bool {
            $passedEntity->setPublicId('ref_123');
            return true; 
        });

        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(UseCaseResponse::class, $response);
        $this->assertEquals('success', $response->getResponse()['refund']);
        $this->assertEquals('ref_123', $response->getResponse()['transaction_id']);
    }

    public function testExecuteRefundFailure()
    {
        $requestData = ['amount' => 100, 'transaction_id' => 'cap_123'];
        $request = new UseCaseRequest($requestData);

        $capture = CaptureBuilder::builder()
            ->withResult('success')
            ->withProvider('providerA')
            ->withAuthorizationId('auth_123')
            ->withPublicId('cap_123')
            ->build();

        $this->captureRepository->method('findByPublicId')->willReturn($capture);
        $this->refundRepository->method('findByCaptureId')->willThrowException(new EntityNotFoundException());

        $refundProvider = $this->createMock(RefundGatewayInterface::class);
        $providerResult = new RefundResult('failure', transactionId: 'ref_123');;
        $refundProvider->method('refund')->willReturn($providerResult);

        $this->providerGatewayFactory->method('createRefundProvider')->willReturn($refundProvider);

        #use callback method to set publicId as it is set in repository
        $this->refundRepository->method('save')->willReturnCallback(function ($passedEntity): bool {
            $passedEntity->setPublicId('ref_123');
            return true; 
        });

        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(UseCaseResponse::class, $response);
        $this->assertEquals('error when processing refund', $response->getResponse()['refund']);
    }

    public function testExecuteRefundAlreadyDone()
    {
        $requestData = ['amount' => 100, 'transaction_id' => 'cap_123'];
        $request = new UseCaseRequest($requestData);

        $capture = CaptureBuilder::builder()
            ->withResult('success')
            ->withProvider('providerA')
            ->withAuthorizationId('auth_123')
            ->withPublicId('cap_123')
            ->build();

        $existingRefund = RefundBuilder::builder()
            ->withResult('success')
            ->withProvider('providerA')
            ->withCaptureId('cap_123')
            ->build();

        $this->captureRepository->method('findByPublicId')->willReturn($capture);
        $this->refundRepository->method('findByCaptureId')->willReturn($existingRefund);

        $this->expectException(UnknownException::class);
        $this->expectExceptionMessage('Already existing refund');

        $this->useCase->execute($request);
    }

    public function testExecuteRepositorySaveFailure()
    {
        $requestData = ['amount' => 100, 'transaction_id' => 'cap_123'];
        $request = new UseCaseRequest($requestData);

        $capture = CaptureBuilder::builder()
            ->withResult('success')
            ->withProvider('providerA')
            ->withAuthorizationId('auth_123')
            ->withPublicId('cap_123')
            ->build();

        $this->captureRepository->method('findByPublicId')->willReturn($capture);
        $this->refundRepository->method('findByCaptureId')->willThrowException(new EntityNotFoundException());

        $refundProvider = $this->createMock(RefundGatewayInterface::class);
        $providerResult = new RefundResult('success', transactionId: 'ref_123');;
        $refundProvider->method('refund')->willReturn($providerResult);
        $this->providerGatewayFactory->method('createRefundProvider')->willReturn($refundProvider);

        $this->refundRepository->method('save')->willReturn(false);

        $this->expectException(UnknownException::class);
        $this->expectExceptionMessage('Unexpected error');

        $this->useCase->execute($request);
    }

    public function testGetRequiredFieldAndValidator()
    {
        $expected = [
            "amount" => AmountValidator::class,
            "transaction_id" => TransactionIdValidator::class,
        ];
        $this->assertEquals($expected, $this->useCase->getRequiredFieldAndValidator());
    }
}