<?php

namespace VestiaireCollective\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use VestiaireCollective\UseCase\UseCaseAbstract;
use VestiaireCollective\UseCase\Request\UseCaseRequest;
use VestiaireCollective\Exception\InvalidFieldException;
use Exception;
use VestiaireCollective\Exception\EntityNotFoundException;
use VestiaireCollective\Enum\HttpCode;
use VestiaireCollective\Exception\MissingFieldException;

class ApiController
{
    public function execute(UseCaseAbstract $useCase, Request $request, Response $response): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strcasecmp($contentType, 'application/json') === 0) {
            $requestBody = $request->getBody(); 
            $data = json_decode($requestBody, true);
        } else {
            $data = $request->getParsedBody();
        }

        try {
            #should have a method that can dynamically create a request based on useCase used
            $useCaseRequest = new UseCaseRequest($data);

            $useCase->isRequestValid($useCaseRequest);

            #echo("Executing use case:" . $useCase::class);
            $useCaseResponse = $useCase->execute($useCaseRequest);

            $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($useCaseResponse->getResponse()));
            return $response->withStatus(HttpCode::OK->value);
        } catch (InvalidFieldException|MissingFieldException $e) {
            return $this->writeExceptionResponse($response, HttpCode::BadRequest->value, $e);
        } catch (EntityNotFoundException $e) {
            return $this->writeExceptionResponse($response, HttpCode::NotFound->value, $e);
        } catch (Exception $e) {
            return $this->writeExceptionResponse($response, HttpCode::InternalServerError->value, $e);
        }
    }

    private function writeExceptionResponse(Response $response, int $httpCode, Exception $e):Response 
    {
        $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($e->getMessage()));

        return $response->withStatus($httpCode);
    }
}

