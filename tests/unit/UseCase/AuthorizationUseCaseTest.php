<?php

use PHPUnit\Framework\TestCase;
use VestiaireCollective\Factory\ProviderGatewayFactoryInterface; 
use VestiaireCollective\Repository\AuthorizationRepositoryInterface;
use VestiaireCollective\Configuration\Configuration;
use VestiaireCollective\Gateway\AuthorizationGatewayInterface;
use VestiaireCollective\UseCase\AuthorizationUseCase;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\Gateway\Result\AuthorizationResult;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use PHPUnit\Framework\MockObject\MockObject;
use VestiaireCollective\Exception\InvalidFieldException;
use VestiaireCollective\Exception\UnknownException;
class AuthorizationUseCaseTest extends TestCase
{
    private AuthorizationUseCase $useCase;

    /** @var ProviderGatewayFactoryInterface|MockObject $providerGatewayFactory */
    private ProviderGatewayFactoryInterface $providerGatewayFactory;

    /** @var AuthorizationRepositoryInterface|MockObject $authorizationRepository */ 
    private AuthorizationRepositoryInterface $authorizationRepository;

    /** @var Configuration|MockObject $config */ 
    private Configuration $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->providerGatewayFactory = $this->createMock(ProviderGatewayFactoryInterface::class);
        $this->authorizationRepository = $this->createMock(AuthorizationRepositoryInterface::class);
        $this->config = $this->createMock(Configuration::class);

        $this->config->method('get')->willReturn(['ProviderA' => 60, 'ProviderB' => 40]);

        $this->useCase = new AuthorizationUseCase(
            $this->config,
            $this->providerGatewayFactory,
            $this->authorizationRepository
        );
    }

    public function testValidRequest()
    {
        $requestData = [
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'expiry_date' => '12/23',
            'cvv' => '123',
        ];

        $useCaseRequest = new UseCaseRequest($requestData);

        $providerGateway = $this->createMock(AuthorizationGatewayInterface::class);
        $providerGateway->method('authorize')->willReturn(new AuthorizationResult('success', 'trx_123', []));

        $this->providerGatewayFactory->method('createAuthorizationProvider')->willReturn($providerGateway);

        #use callback method to set publicId as it is set in repository
        $this->authorizationRepository->method('save')->willReturnCallback(function ($passedEntity): bool {
            $passedEntity->setPublicId('trx_123');
            return true; 
        });
        $response = $this->useCase->execute($useCaseRequest);

        $this->assertInstanceOf(UseCaseResponse::class, $response);
        $this->assertEquals('success', $response->getResponse()['status']);
        $this->assertEquals('trx_123', $response->getResponse()['auth_token']);
    }

    public function testInvalidCVV()
    {
        $requestData = [
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'expiry_date' => '12/23',
            'cvv' => '5154',
        ];

        $useCaseRequest = new UseCaseRequest($requestData);

        $this->expectException(InvalidFieldException::class);
        $this->useCase->isRequestValid($useCaseRequest);
    }

    public function testProviderSelectionFailure()
    {
        $this->config = $this->createMock(Configuration::class);
        $this->config->method('get')->willReturn([]);
        $this->useCase = new AuthorizationUseCase(
            $this->config,
            $this->providerGatewayFactory,
            $this->authorizationRepository
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Could not select a provider");
        $this->useCase->execute(new UseCaseRequest([]));
    }


    public function testAuthorizationFailure()
    {
        $requestData = [
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'expiry_date' => '12/23',
            'cvv' => '123',
        ];

        $useCaseRequest = new UseCaseRequest($requestData);

        $providerGateway = $this->createMock(AuthorizationGatewayInterface::class);
        $providerGateway->method('authorize')->willReturn(new AuthorizationResult('failure', 'trx_123', []));

        $this->providerGatewayFactory->method('createAuthorizationProvider')->willReturn($providerGateway);
        $this->authorizationRepository->method('save')->willReturnCallback(function ($passedEntity): bool {
            $passedEntity->setPublicId('trx_123');
            return true; 
        });


        $response = $this->useCase->execute($useCaseRequest);

        $this->assertInstanceOf(UseCaseResponse::class, $response);
        $this->assertEquals('failed', $response->getResponse()['status']);
        $this->assertNotEmpty($response->getResponse()['auth_token']);
    }

    public function testRepositorySaveFailure()
    {
        $requestData = [
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'expiry_date' => '12/23',
            'cvv' => '123',
        ];

        $useCaseRequest = new UseCaseRequest($requestData);

        $providerGateway = $this->createMock(AuthorizationGatewayInterface::class);
        $providerGateway->method('authorize')->willReturn(new AuthorizationResult('sucess', 'trx_123', []));

        $this->providerGatewayFactory->method('createAuthorizationProvider')->willReturn($providerGateway);
        $this->authorizationRepository->method('save')->willReturn(false);

        $this->expectException(UnknownException::class);
        $this->expectExceptionMessage('Unexpected error');
        $this->useCase->execute($useCaseRequest);
    }


}