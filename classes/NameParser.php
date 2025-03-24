<?php

namespace App;

use App\Contracts\NameParserInterface;
use App\Contracts\NameParsingStrategy;
use App\Exceptions\NameParsingException;
use App\Strategies\MultipleNameStrategy;
use App\Strategies\SingleNameStrategy;

/**
 * Class NameParser
 *
 * Handles parsing of full name strings into structured data.
 */
class NameParser implements NameParserInterface
{
    /** @var NameParsingStrategy[] */
    public function __construct(private array $strategies)
    {
        $this->strategies = $strategies ?? [
            new SingleNameStrategy(),
            new MultipleNameStrategy()
        ];
    }

    /**
     * Parse a name string into an array of structured data
     *
     * @param string $nameString
     * @return array Each element is an associative array with person details
     * @throws NameParsingException
     */
    public function parse(string $nameString): array
    {
        foreach ($this->strategies as $strategy) {
            try {
                if ($strategy->canParse($nameString)) {
                    return $strategy->parse($nameString);
                }
            } catch (NameParsingException $e) {
                // If one strategy fails to parse, try the next one
                continue;
            }
        }

        throw NameParsingException::invalidFormat($nameString);
    }
}