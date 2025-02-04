<?php

namespace VestiaireCollective\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\MockObject\MockObject;
use VestiaireCollective\Controller\ApiController;
use VestiaireCollective\UseCase\UseCaseAbstract;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\UseCase\Response\UseCaseResponse;
use VestiaireCollective\Enum\HttpCode;
use VestiaireCollective\Exception\InvalidFieldException;
use VestiaireCollective\Exception\MissingFieldException;
use VestiaireCollective\Exception\EntityNotFoundException;
use Exception;

class ApiControllerTest extends TestCase
{
    private ApiController $controller;

    /** @var UseCaseAbstract|MockObject $useCase */
    private UseCaseAbstract $useCase;

    /** @var ServerRequestInterface|MockObject $request */
    private ServerRequestInterface $request;

    /** @var ResponseInterface|MockObject $response */
    private ResponseInterface $response;

    /** @var StreamInterface|MockObject $body */
    private StreamInterface $body;
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ApiController();

        $this->useCase = $this->createMock(UseCaseAbstract::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->body = $this->createMock(StreamInterface::class);

    }

    public function testExecuteSuccess()
    {
        $requestData = ['test' => 'data'];
        $useCaseRequest = new UseCaseRequest($requestData);
        $this->request->method('getParsedBody')->willReturn($requestData);

        $useCaseResponseData = ['status' => 'success'];
        $useCaseResponse = new UseCaseResponse($useCaseResponseData);
        $this->useCase->method('execute')->willReturn($useCaseResponse);

        $result = $this->controller->execute($this->useCase, $this->request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testExecuteInvalidFieldException()
    {
        $useCase = $this->createMock(UseCaseAbstract::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $requestData = ['test' => 'data'];
        $useCaseRequest = new UseCaseRequest($requestData);
        $request->method('getParsedBody')->willReturn($requestData);
        $useCase->method('isRequestValid')->willThrowException(new InvalidFieldException("Invalid field"));

        $result = $this->controller->execute($useCase, $request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testExecuteMissingFieldException()
    {
        $useCase = $this->createMock(UseCaseAbstract::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $requestData = ['test' => 'data'];
        $useCaseRequest = new UseCaseRequest($requestData);
        $request->method('getParsedBody')->willReturn($requestData);
        $useCase->method('isRequestValid')->willThrowException(new MissingFieldException("Missing field"));

        $result = $this->controller->execute($useCase, $request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }


    public function testExecuteEntityNotFoundException()
    {
        $useCase = $this->createMock(UseCaseAbstract::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $requestData = ['test' => 'data'];
        $useCaseRequest = new UseCaseRequest($requestData);
        $request->method('getParsedBody')->willReturn($requestData);

        $useCase->method('execute')->willThrowException(new EntityNotFoundException("Entity not found"));

        $result = $this->controller->execute($useCase, $request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testExecuteGenericException()
    {
        $useCase = $this->createMock(UseCaseAbstract::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $requestData = ['test' => 'data'];
        $useCaseRequest = new UseCaseRequest($requestData);
        $request->method('getParsedBody')->willReturn($requestData);
        $useCase->method('execute')->willThrowException(new Exception("Generic exception"));

        $result = $this->controller->execute($useCase, $request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
