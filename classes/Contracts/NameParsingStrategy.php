<?php

namespace App\Contracts;

use App\Exceptions\NameParsingException;

/**
 * Interface NameParsingStrategy
 *
 * Contract for name parsing strategies. Any implementation must:
 * 1. Return an array of associative arrays for parsed names
 * 2. Throw NameParsingException for any parsing errors
 * 3. Handle empty or invalid input consistently
 */
interface NameParsingStrategy
{
    /**
     * Check if this strategy can handle the given name string
     *
     * @param string $nameString
     * @return bool
     * @throws NameParsingException if the input is empty or invalid
     */
    public function canParse(string $nameString): bool;

    /**
     * Parse the name string into an array of person details
     *
     * @param string $nameString
     * @return array Each element must be an associative array with keys:
     *               - title (string)
     *               - firstName (string|null)
     *               - initials (string|null)
     *               - lastName (string)
     * @throws NameParsingException if parsing fails or required data is missing
     */
    public function parse(string $nameString): array;

    /**
     * Validate the parsed result structure
     *
     * @param array $result
     * @return bool
     */
    public function validateResult(array $result): bool;
} 