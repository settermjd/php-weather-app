<?php

declare(strict_types=1);

namespace Weather\Error;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Interfaces\ErrorRendererInterface;
use Slim\Views\Twig;
use Throwable;

final readonly class HtmlErrorRenderer implements ErrorHandlerInterface
{
    public function __construct(private readonly Twig $twig, private readonly App $app) { }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface
    {
        $title = 'Error';
        $message = 'An error has occurred.';

        if ($exception instanceof HttpNotFoundException) {
            $title = $exception->getTitle();
            $message = $exception->getMessage();
        }

        return $this->renderHtmlPage($request, $title, $message);
    }

    public function renderHtmlPage(
        ServerRequestInterface $request,
        string $title = '',
        string $message = ''
    ): ResponseInterface
    {
        $title = htmlentities($title, ENT_COMPAT|ENT_HTML5, 'utf-8');
        $message = htmlentities($message, ENT_COMPAT|ENT_HTML5, 'utf-8');
        return $this->twig
            ->render(
                $this->app->getResponseFactory()->createResponse(),
                '404.html.twig',
                [
                    'title' => $title,
                    'message' => $message,
                ]
            );
    }
}