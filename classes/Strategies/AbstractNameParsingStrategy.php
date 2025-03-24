<?php

namespace App\Strategies;

use App\Contracts\NameParsingStrategy;
use App\Exceptions\NameParsingException;
use App\Traits\TitleValidator;

abstract class AbstractNameParsingStrategy implements NameParsingStrategy
{
    use TitleValidator;

    /**
     * Validate the parsed result structure
     *
     * @param array $result
     * @return bool
     * @throws NameParsingException if validation fails
     */
    public function validateResult(array $result): bool
    {
        foreach ($result as $person) {
            if (!is_array($person)) {
                throw NameParsingException::validationError("Each result must be an array");
            }

            // Required fields
            $requiredKeys = ['title', 'lastName'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $person)) {
                    throw NameParsingException::validationError("Missing required key: {$key}");
                }
            }

            // Optional fields
            $optionalKeys = ['firstName', 'initials'];
            foreach ($optionalKeys as $key) {
                if (!array_key_exists($key, $person)) {
                    $person[$key] = null;
                }
            }

            // Validate required fields
            if (!is_string($person['title']) || empty($person['title'])) {
                throw NameParsingException::validationError("Title must be a non-empty string");
            }

            if (!is_string($person['lastName']) || empty($person['lastName'])) {
                throw NameParsingException::validationError("Last name must be a non-empty string");
            }

            // Validate optional fields if they are present
            if (isset($person['firstName']) && !is_null($person['firstName']) && !is_string($person['firstName'])) {
                throw NameParsingException::validationError("First name must be null or string");
            }

            if (isset($person['initials']) && !is_null($person['initials']) && !is_string($person['initials'])) {
                throw NameParsingException::validationError("Initials must be null or string");
            }
        }

        return true;
    }

    /**
     * Validate input string
     *
     * @param string $nameString
     * @throws NameParsingException if input is invalid
     */
    protected function validateInput(string $nameString): void
    {
        if (empty(trim($nameString))) {
            throw NameParsingException::emptyInput();
        }
    }
} 