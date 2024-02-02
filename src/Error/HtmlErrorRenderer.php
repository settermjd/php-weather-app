<?php

declare(strict_types=1);

namespace Weather\Error;

use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorRendererInterface;
use Slim\Views\Twig;
use Throwable;

final readonly class HtmlErrorRenderer implements ErrorRendererInterface
{
    public function __construct(private readonly Twig $twig) { }

    public function __invoke(
        Throwable $exception,
        bool $displayErrorDetails,
    ): string
    {
        if ($exception instanceof HttpNotFoundException) {
            $title = $exception->getTitle();
            $message = $exception->getMessage();
            $template = '404.html.twig';
        } else {
            $title = 'Oh no! Something went wrong.';
            $message = sprintf(
                "%s.",
                htmlentities($exception->getMessage(), ENT_COMPAT|ENT_HTML5, 'utf-8')
            );
            $template = 'error.html.twig';
        }

        return $this->twig
            ->fetch(
                $template,
                [
                    'title' => $title,
                    'message' => $message,
                    'exception' => $exception,
                ]
            );
    }
}