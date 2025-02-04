<?php

use PHPUnit\Framework\TestCase;
use VestiaireCollective\UseCase\CaptureUseCase;
use VestiaireCollective\Repository\AuthorizationRepositoryInterface;
use VestiaireCollective\Repository\CaptureRepositoryInterface;
use VestiaireCollective\Factory\ProviderGatewayFactoryInterface;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\Entity\Builder\AuthorizationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use VestiaireCollective\Exception\EntityNotFoundException;
use VestiaireCollective\Gateway\CaptureGatewayInterface;
use VestiaireCollective\Gateway\Result\CaptureResult;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\Entity\Builder\CaptureBuilder;
use VestiaireCollective\Exception\UnknownException;
class CaptureUseCaseTest extends TestCase
{
    private CaptureUseCase $useCase;

    /** @var AuthorizationRepositoryInterface|MockObject $authorizationRepository */ 
    private AuthorizationRepositoryInterface $authorizationRepository;

    /** @var CaptureRepositoryInterface|MockObject $captureRepository */ 
    private CaptureRepositoryInterface $captureRepository;

    /** @var ProviderGatewayFactoryInterface|MockObject $providerGatewayFactory */ 
    private ProviderGatewayFactoryInterface $providerGatewayFactory;


    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizationRepository = $this->createMock(AuthorizationRepositoryInterface::class);
        $this->captureRepository = $this->createMock(CaptureRepositoryInterface::class);
        $this->providerGatewayFactory = $this->createMock(ProviderGatewayFactoryInterface::class);

        $this->useCase = new CaptureUseCase(
            $this->providerGatewayFactory,
            $this->authorizationRepository,
            $this->captureRepository
        );
    }

    public function testExecuteSuccess()
    {
        $requestData = ['auth_token' => 'auth_123', 'amount' => 100];
        $request = new UseCaseRequest($requestData);

        $authorization = AuthorizationBuilder::builder()
            ->withAmount(100)
            ->withCardNumber('4111111111111111')
            ->withExpiryDate('11/28')
            ->withCvv('414')
            ->withResult('success')
            ->withProvider('providerA')
            ->withPublicId('auth_123')
            ->withId(1)
            ->build();

        $this->authorizationRepository->method('findByPublicId')->willReturn($authorization);
        $this->captureRepository->method('findByAuthorizationId')->willThrowException(new EntityNotFoundException());

        $captureProvider = $this->createMock(CaptureGatewayInterface::class);
        $providerResult = new CaptureResult('success', transactionId: 'trx_123');
        $captureProvider->method('capture')->willReturn($providerResult);

        $this->providerGatewayFactory->method('createCaptureProvider')->willReturn($captureProvider);

        #use callback method to set publicId as it is set in repository
        $this->captureRepository->method('save')->willReturnCallback(function ($passedEntity): bool {
            $passedEntity->setPublicId('trx_123');
            return true; 
        });


        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(UseCaseResponse::class, $response);
        $this->assertEquals('success', $response->getResponse()['status']);
        $this->assertEquals('trx_123', $response->getResponse()['transaction_id']);
    }

    public function testExecuteFailure()
    {
        $requestData = ['auth_token' => 'auth_123', 'amount' => 100];
        $request = new UseCaseRequest($requestData);

        $authorization = AuthorizationBuilder::builder()
            ->withAmount(100)
            ->withCardNumber('4111111111111111')
            ->withExpiryDate('11/28')
            ->withCvv('414')
            ->withResult('success')
            ->withProvider('providerA')
            ->withPublicId('auth_123')
            ->withId(1)
            ->build();

        $this->authorizationRepository->method('findByPublicId')->willReturn($authorization);
        $this->captureRepository->method('findByAuthorizationId')->willThrowException(new EntityNotFoundException());

        $captureProvider = $this->createMock(CaptureGatewayInterface::class);
        $providerResult = new CaptureResult('failure', transactionId: 'trx_123');
        $captureProvider->method('capture')->willReturn($providerResult);

        $this->providerGatewayFactory->method('createCaptureProvider')->willReturn($captureProvider);

        #use callback method to set publicId as it is set in repository
        $this->captureRepository->method('save')->willReturnCallback(function ($passedEntity): bool {
            $passedEntity->setPublicId('trx_123');
            return true; 
        });

        $response = $this->useCase->execute($request);

        $this->assertInstanceOf(UseCaseResponse::class, $response);
        $this->assertEquals('failed', $response->getResponse()['status']);
        $this->assertEquals('trx_123', $response->getResponse()['transaction_id']);
    }

    public function testExecuteCaptureAlreadyDone()
    {
        $requestData = ['auth_token' => 'auth_123', 'amount' => 100];
        $request = new UseCaseRequest($requestData);

        $authorization = AuthorizationBuilder::builder()
            ->withAmount(100)
            ->withCardNumber('4111111111111111')
            ->withExpiryDate('11/28')
            ->withCvv('414')
            ->withResult('success')
            ->withProvider('providerA')
            ->withPublicId('auth_123')
            ->withId(1)
            ->build();

        $existingCapture = CaptureBuilder::builder()
            ->withResult('success')
            ->withProvider('providerA')
            ->withAuthorizationId($authorization->getPublicId())
            ->build();

        $this->authorizationRepository->method('findByPublicId')->willReturn($authorization);
        $this->captureRepository->method('findByAuthorizationId')->willReturn($existingCapture);

        $this->expectException(UnknownException::class);
        $this->expectExceptionMessage('Already existing capture');

        $this->useCase->execute($request);
    }

    public function testExecuteRepositorySaveFailure()
    {
        $requestData = ['auth_token' => 'auth_123', 'amount' => 100];
        $request = new UseCaseRequest($requestData);

        $authorization = AuthorizationBuilder::builder()
            ->withAmount(100)
            ->withCardNumber('4111111111111111')
            ->withExpiryDate('11/28')
            ->withCvv('414')
            ->withResult('success')
            ->withProvider('providerA')
            ->withPublicId('auth_123')
            ->withId(1)
            ->build();


        $this->authorizationRepository->method('findByPublicId')->willReturn($authorization);
        $this->captureRepository->method('findByAuthorizationId')->willThrowException(new EntityNotFoundException());

        $captureProvider = $this->createMock(CaptureGatewayInterface::class);
        $providerResult = new CaptureResult('failed', transactionId: 'trx_123');
        $captureProvider->method('capture')->willReturn($providerResult);

        $this->providerGatewayFactory->method('createCaptureProvider')->willReturn($captureProvider);

        $this->captureRepository->method('save')->willReturn(false);

        $this->expectException(UnknownException::class);
        $this->expectExceptionMessage('Unexpected error');

        $this->useCase->execute($request);
    }
}