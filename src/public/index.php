<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Controllers\FlatsController;
use Controllers\SalesController;

require_once __DIR__ . '/../../vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->add(function (Request $request, $handler): Response {
    $response = $handler->handle($request);
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->get('/', function (Request $request, Response $response) {
   $response->getBody()->write('Hello World!');
   return $response;
});

$app->get('/flats', [FlatsController::class,"GetAllWithFilter"]);
$app->get('/flats/{id}', [FlatsController::class,'Get']);
$app->get("/flats-boundary-values", [FlatsController::class, "GetStartBoundaryValues"]);
$app->get('/sales', [SalesController::class,'GetAll']);

$app->run();