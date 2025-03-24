<?php

namespace App\Exceptions;

class NameParsingException extends \Exception
{
    public const EMPTY_INPUT = 1;
    public const INVALID_TITLE = 2;
    public const MISSING_LAST_NAME = 3;
    public const INVALID_FORMAT = 4;
    public const VALIDATION_ERROR = 5;

    /**
     * Create a new exception instance
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }

    /**
     * Create an exception for empty input
     *
     * @return self
     */
    public static function emptyInput(): self
    {
        return new self("Empty name string provided", self::EMPTY_INPUT);
    }

    /**
     * Create an exception for invalid title
     *
     * @param string $title
     * @return self
     */
    public static function invalidTitle(string $title): self
    {
        return new self("Invalid title detected: {$title}", self::INVALID_TITLE);
    }

    /**
     * Create an exception for missing last name
     *
     * @param string $nameString
     * @return self
     */
    public static function missingLastName(string $nameString): self
    {
        return new self("Unable to determine last name for: {$nameString}", self::MISSING_LAST_NAME);
    }

    /**
     * Create an exception for invalid format
     *
     * @param string $nameString
     * @return self
     */
    public static function invalidFormat(string $nameString): self
    {
        return new self("Invalid name format: {$nameString}", self::INVALID_FORMAT);
    }

    /**
     * Create an exception for validation error
     *
     * @param string $details
     * @return self
     */
    public static function validationError(string $details): self
    {
        return new self("Validation error: {$details}", self::VALIDATION_ERROR);
    }
} 