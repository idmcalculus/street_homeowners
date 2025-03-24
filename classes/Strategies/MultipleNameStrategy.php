<?php

namespace App\Strategies;

use App\Exceptions\NameParsingException;
use App\Person;
use App\Traits\TitleValidator;

class MultipleNameStrategy extends AbstractNameParsingStrategy
{
    use TitleValidator;

    public function canParse(string $nameString): bool
    {
        $this->validateInput($nameString);
        return preg_match('/\s+(and|&)\s+/', $nameString);
    }

    public function parse(string $nameString): array
    {
        $names = preg_split('/\s+(?:and|&)\s+/', $nameString);
        
        if (count($names) == 2 && $this->isTitleOnly(trim($names[0]))) {
            $result = $this->handleSpecialCases($names);
        } else {
            $commonLastName = $this->getCommonLastName($names);
            $result = array_map(function($name) use ($commonLastName) {
                return $this->parseSingleName($name, $commonLastName)->toArray();
            }, $names);
        }

        $this->validateResult($result);
        return $result;
    }

    private function handleSpecialCases(array $names): array
    {
        $titleOnly = trim($names[0]);
        $secondPart = trim($names[1]);
        $words = array_filter(explode(' ', $secondPart));
        
        if (count($words) == 2 && $this->isValidTitle($this->standardizeTitle($words[0]))) {
            return $this->handleTitleAndLastName($titleOnly, $words);
        }
        
        if (!empty($words)) {
            return $this->handleTitleAndFullName($titleOnly, $words);
        }

        throw NameParsingException::invalidFormat(implode(' and ', $names));
    }

    private function handleTitleAndLastName(string $titleOnly, array $words): array
    {
        $title1 = $this->standardizeTitle($titleOnly);
        $title2 = $this->standardizeTitle($words[0]);
        $lastName = $words[1];
        
        try {
            $this->validateTitle($title1);
            $this->validateTitle($title2);
        } catch (\Exception $e) {
            throw NameParsingException::invalidTitle($e->getMessage());
        }
        
        return [
            (new Person($title1, null, null, $lastName))->toArray(),
            (new Person($title2, null, null, $lastName))->toArray()
        ];
    }

    private function handleTitleAndFullName(string $titleOnly, array &$words): array
    {
        $title1 = $this->standardizeTitle($titleOnly);
        $title2 = $this->standardizeTitle(array_shift($words));
        
        try {
            $this->validateTitle($title1);
            $this->validateTitle($title2);
        } catch (\Exception $e) {
            throw NameParsingException::invalidTitle($e->getMessage());
        }

        $firstName = array_shift($words);
        $lastName = count($words) ? array_pop($words) : null;
        
        if (!$lastName) {
            throw NameParsingException::missingLastName(implode(' ', [$titleOnly, $title2, $firstName]));
        }
        
        return [
            (new Person($title1, $firstName, null, $lastName))->toArray(),
            (new Person($title2, null, null, $lastName))->toArray()
        ];
    }

    private function getCommonLastName(array $names): string
    {
        if (count($names) == 2 && $this->isTitleOnly(trim($names[0])) && $this->isTitleOnly(trim($names[1]))) {
            throw NameParsingException::missingLastName(implode(' and ', $names));
        }
        
        foreach (array_reverse($names) as $name) {
            $parts = array_filter(explode(' ', trim($name)));
            if (count($parts) > 1) {
                return array_pop($parts);
            }
        }
        
        throw NameParsingException::missingLastName(implode(' and ', $names));
    }

    private function isTitleOnly(string $name): bool
    {
        return in_array(rtrim($name, '.'), ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Mister', 'Master', 'Sir']);
    }

    private function parseSingleName(string $nameString, string $defaultLastName): Person
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
        $lastName = $defaultLastName;

        if (!empty($parts)) {
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
        }

        return new Person($title, $firstName, $initials, $lastName);
    }
} 