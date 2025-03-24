<?php

namespace App\Traits;

trait TitleValidator
{
    /**
     * List of valid titles
     *
     * @var array
     */
    private static array $validTitles = ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Sir'];

    /**
     * Standardizes a title string (e.g., converts 'Mister' to 'Mr').
     *
     * @param string $title
     * @return string
     */
    protected function standardizeTitle(string $title): string
    {
        $cleanTitle = rtrim($title, '.');
        return ($cleanTitle === 'Mister' || $cleanTitle === 'Master') ? 'Mr' : $cleanTitle;
    }

    /**
     * Validates the title against a list of allowed titles.
     *
     * @param string $title
     * @throws \Exception If the title is invalid.
     */
    protected function validateTitle(string $title): void
    {
        if (!$this->isValidTitle($title)) {
            throw new \Exception("Invalid title detected: {$title}");
        }
    }

    /**
     * Checks if a title is valid without throwing an exception.
     *
     * @param string $title The title to validate.
     * @return bool True if the title is valid, false otherwise.
     */
    protected function isValidTitle(string $title): bool
    {
        return in_array($title, self::$validTitles);
    }
} 