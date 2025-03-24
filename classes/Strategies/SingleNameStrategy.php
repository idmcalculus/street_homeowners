<?php

namespace App\Strategies;

use App\Exceptions\NameParsingException;
use App\Person;
use App\Traits\TitleValidator;

class SingleNameStrategy extends AbstractNameParsingStrategy
{
    use TitleValidator;

    public function canParse(string $nameString): bool
    {
        $this->validateInput($nameString);
        return !preg_match('/\s+(and|&)\s+/', $nameString);
    }

    public function parse(string $nameString): array
    {
        $result = [$this->parseSingleName($nameString)->toArray()];
        $this->validateResult($result);
        return $result;
    }

    private function parseSingleName(string $nameString): Person
    {
        $parts = array_filter(explode(' ', trim($nameString)));
        if (empty($parts)) {
            throw NameParsingException::emptyInput();
        }

        $title = $this->standardizeTitle(array_shift($parts));
        try {
            $this->validateTitle($title);
        } catch (\Exception $e) {
            throw NameParsingException::invalidTitle($title);
        }

        $firstName = null;
        $initials = null;
        $lastName = null;

        if (count($parts) === 1) {
            $lastName = array_shift($parts);
        } else {
            $nextToken = reset($parts);
            if (strlen($nextToken) > 1 && strpos($nextToken, '.') === false) {
                $firstName = array_shift($parts);
            }

            $possibleInitials = [];
            while (!empty($parts) && (strlen(reset($parts)) === 1 || (strlen(reset($parts)) === 2 && substr(reset($parts), -1) === '.'))) {
                $possibleInitials[] = rtrim(array_shift($parts), '.');
            }
            if (!empty($possibleInitials)) {
                $initials = implode(", ", $possibleInitials);
            }

            if (!empty($parts)) {
                $lastName = array_pop($parts);
            }
        }

        if (!$lastName) {
            throw NameParsingException::missingLastName($nameString);
        }

        return new Person($title, $firstName, $initials, $lastName);
    }
} 