<?php

namespace App\Contracts;

use App\Exceptions\NameParsingException;

interface NameParserInterface
{
    /**
     * Parse a name string into an array of structured data
     *
     * @param string $nameString
     * @return array Each element is an associative array with person details
     * @throws NameParsingException
     */
    public function parse(string $nameString): array;
} 