<?php

declare(strict_types=1);

use BomWeather\BomClient;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\NullLogger;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('view', function() {
    return Twig::create(__DIR__ . '/../templates', []);
});
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->add(TwigMiddleware::createFromContainer($app));
$app->get('/[{city}]', function (Request $request, Response $response, array $args) {
    $city = $args['city'] ?? '';

    $logger = new NullLogger();
    $client = new BomClient($logger);
    $forecast = $client->getForecast('IDN10031');

    return $this->get('view')
        ->render($response, 'default.html.twig', ['city' => $city]);
});

$app->run();
