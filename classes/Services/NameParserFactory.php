<?php

namespace App\Services;

use App\Contracts\NameParserInterface;
use App\NameParser;
use App\Strategies\MultipleNameStrategy;
use App\Strategies\SingleNameStrategy;

class NameParserFactory
{
    /**
     * Create a new name parser instance with default strategies
     *
     * @return NameParserInterface
     */
    public static function create(): NameParserInterface
    {
        return new NameParser([
            new SingleNameStrategy(),
            new MultipleNameStrategy()
        ]);
    }

    /**
     * Create a new name parser instance with custom strategies
     *
     * @param array $strategies Array of NameParsingStrategy instances
     * @return NameParserInterface
     */
    public static function createWithStrategies(array $strategies): NameParserInterface
    {
        return new NameParser($strategies);
    }
} 