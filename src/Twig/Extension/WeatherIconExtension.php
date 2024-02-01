<?php

namespace Weather\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class WeatherIconExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getWeatherIcon', [$this, 'getIcon'])
        ];
    }

    public function getIcon(?int $weatherCode = null): string
    {
        return match($weatherCode) {
            200, 201, 202, 210, 211, 212, 221, 230, 231, 232 => 'thunderstorm',
            300, 301, 302, 310, 311, 312, 313, 314, 321 => 'drizzle',
            500, 501, 502, 503, 504, 511, 520, 521, 522, 531 => 'rain',
            600, 601, 602, 611, 612, 613, 615, 616, 620, 621, 622 => 'snow',
            701 => 'mist',
            711 => 'smoke',
            721 => 'haze',
            731, 761 => 'dust',
            741 => 'fog',
            781 => 'tornado',
            800 => 'clear',
            801, 802, 803, 804 => 'clouds',
            default => 'unknown'
        };
    }
}