<?php

declare(strict_types=1);

namespace Weather\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WindDirectionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getWindDirection', [$this, 'getWindDirectionPhraseFromCode']),
        ];
    }

    public function getWindDirectionPhraseFromCode(string $windDirection): string
    {
        return match($windDirection) {
            // Cardinal Directions or Principal Winds
            'N' => 'North',
            'S' => 'South',
            'E' => 'East',
            'W' => 'West',

            // Intermediate Directions or Ordinal Directions or Half-winds
            'NE' => 'North East',
            'SE' => 'South East',
            'SW' => 'South West',
            'NW' => 'North West',

            // Secondary Intercardinal Directions or Quarter-winds
            'NNE' => 'North North East',
            'ENE' => 'East North East',
            'ESE' => 'East South East',
            'SSW' => 'South South West',
            'WSW' => 'West South West',
            'WNW' => 'West North West',
            'NNW' => 'North North West'
        };
    }
}