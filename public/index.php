<?php

declare(strict_types=1);

session_start();

use Cmfcmf\OpenWeatherMap\Exception as OWMException;
use Cmfcmf\OpenWeatherMap;
use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Weather\Error\HtmlErrorRenderer;
use Weather\Twig\Extension\WeatherIconExtension;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();
$dotenv->required('OPENWEATHERMAP_APIKEY');

$container = new Container();
$container->set('view', function(): Twig {
    $twig = Twig::create(__DIR__ . '/../templates', []);
    $twig->addExtension(new WeatherIconExtension());

    return $twig;
});

$container->set('logger', function(): LoggerInterface {
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler(__DIR__ . '/var/log', Level::Debug));
    return $log;
});

$container->set('flash', function () {
    return new Messages();
});
AppFactory::setContainer($container);

$app = AppFactory::create();

// Add middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Add routes
$app->get('/', function (Request $request, Response $response, array $args) {
    /** @var Messages $flash */
    $flash = $this->get('flash');
    return $this->get('view')
        ->render(
            $response,
            'home.html.twig',
            [
                'error' => $flash->getMessage('error'),
            ]
        );
});

$app->post('/', function (Request $request, Response $response, array $args) {
    $city = $request->getParsedBody()['city'] ?? '';
    if ($city === '') {
        $flash = $this->get('flash');
        $flash->addMessage('error', 'Please provide a city');
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    $owm = new OpenWeatherMap(
        $_ENV["OPENWEATHERMAP_APIKEY"],
        new GuzzleHttp\Client(),
        new RequestFactory()
    );

    $data = ['city' => $city];

    try {
        $unit = $request->getParsedBody()['unit'] ?? 'metric';
        $weather = $owm->getWeather($city, $unit, 'en');
        $data['weather'] = $weather;
        $uv = $owm->getCurrentUVIndex($weather->city->lat, $weather->city->lon);
        $data['uv'] = $uv;
    } catch(OWMException $e) {
        /** @var LoggerInterface $log */
        $log = $this->get('logger');
        $log->error('OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').');
        throw new HttpNotFoundException(
            $request,
            'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').',
            $e
        );
    } catch(\Exception $e) {
        /** @var LoggerInterface $log */
        $log = $this->get('logger');
        $log->error('General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').');
        throw new HttpNotFoundException(
            $request,
            'General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').',
            $e
        );
    }

    return $this->get('view')
        ->render(
            $response,
            'weather-report.html.twig',
            ['data' => $data]
        );
});

$displayErrorDetails = (bool)($_ENV['DEBUG'] ?? false);
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->registerErrorRenderer('text/html', new HtmlErrorRenderer($container->get('view'), $app));

$app->run();