<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use VestiaireCollective\Repository\AuthorizationRepository;
use Slim\Builder\AppBuilder;
use Slim\Middleware\EndpointMiddleware;
use Slim\Middleware\ExceptionHandlingMiddleware;
use Slim\Middleware\RoutingMiddleware;
use VestiaireCollective\Factory\ProviderGatewayFactory;
use VestiaireCollective\Repository\CaptureRepository;
use VestiaireCollective\Repository\RefundRepository;
use VestiaireCollective\UseCase\AuthorizationUseCase;
use VestiaireCollective\Controller\ApiController;
use VestiaireCollective\UseCase\CaptureUseCase;
use VestiaireCollective\Configuration\Configuration;
use VestiaireCollective\UseCase\RefundUseCase;
use Dotenv\Dotenv;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ResponseFactoryMiddleware;

// Instantiate App using the builder
$builder = new AppBuilder();
$app = $builder->build();

// Add middleware
$app->add(RoutingMiddleware::class);
$app->add(EndpointMiddleware::class);
$app->add(BodyParsingMiddleware::class);
$app->add(ResponseFactoryMiddleware::class);

// Instatiate dotenv
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Authorization Endpoint
$app->post('/authorize',function (Request $request, Response $response) 
{
    $useCase = new AuthorizationUseCase(
        Configuration::getInstance(),
        new ProviderGatewayFactory(),
        new AuthorizationRepository(
            new Predis\Client([
            'type' => 'tcp', 
            'host' => $_ENV['REDIS_HOST'] ?: '127.0.0.1',
            'port' => $_ENV['REDIS_PORT'] ?: 6379,
            'password' => $_ENV['REDIS_PASSWORD'] ?: '',
            'database' => $_ENV['REDIS_DATABASE'] ?: 0,
        ]))
    );

    $apiController = new ApiController();

    return $apiController->execute($useCase, $request, $response);
});

//Capture Endpoint
$app->post('/capture', function (Request $request, Response $response) 
{
    $client = new Predis\Client([
        'type' => 'tcp', 
        'host' => $_ENV['REDIS_HOST'] ?: '127.0.0.1',
        'port' => $_ENV['REDIS_PORT'] ?: 6379,
        'password' => $_ENV['REDIS_PASSWORD'] ?: '',
        'database' => $_ENV['REDIS_DATABASE'] ?: 0,
    ]);

    $useCase = new CaptureUseCase(
        new ProviderGatewayFactory(),
        new AuthorizationRepository($client),
        new CaptureRepository($client)
    );

    $apiController = new ApiController();

    return $apiController->execute($useCase, $request, $response);;
});


//Refund Endpoint
$app->post('/refund', function (Request $request, Response $response) 
{
    $client = new Predis\Client([
        'type' => 'tcp', 
        'host' => $_ENV['REDIS_HOST'] ?: '127.0.0.1',
        'port' => $_ENV['REDIS_PORT'] ?: 6379,
        'password' => $_ENV['REDIS_PASSWORD'] ?: '',
        'database' => $_ENV['REDIS_DATABASE'] ?: 0,
    ]);

    $useCase = new RefundUseCase(
        new ProviderGatewayFactory(),
        new CaptureRepository($client),
        new RefundRepository($client)
    );

    $apiController = new ApiController();

    return $apiController->execute($useCase, $request, $response);;
});

$app->run();