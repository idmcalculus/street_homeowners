<?php

namespace App;

/**
 * Class Person
 *
 * Represents an individual with a title, first name, initials, and last name.
 * Provides functionality to parse full name strings into structured data.
 */
class Person {
	public function __construct(
		public string $title,
		public ?string $firstName,
		public ?string $initials,
		public string $lastName
	) {}

	/**
	 * Converts the Person instance to an associative array, which is our expected output format
	 *
	 * @return array
	 */
	public function toArray(): array {
		return [
			'title'     => $this->title,
			'firstName' => $this->firstName,
			'initials'  => $this->initials,
			'lastName'  => $this->lastName,
		];
	}
	
	/**
	 * Parses a full name string into an array of person details.
	 *
	 * @param string $nameString The full name string to parse.
	 * @return array An array of associative arrays, each representing a person's details.
	 * @throws \Exception If the name cannot be parsed.
	 */
	public static function parseNameString(string $nameString): array {
		if (preg_match('/\s+(and|&)\s+/', $nameString)) {
			return self::parseMultipleNames($nameString);
		}
		// Always return array of arrays for consistency
		return [self::parseSingleName($nameString)->toArray()];
	}
	
	/**
	 * Parses multiple names from a combined name string.
	 *
	 * @param string $nameString The combined name string.
	 * @return array An array of associative arrays for each person.
	 * @throws \Exception When a common last name cannot be determined.
	 */
	private static function parseMultipleNames(string $nameString): array {
		// Split using a simple regex to account for both " and " and " & "
		$names = preg_split('/\s+(?:and|&)\s+/', $nameString);
		
		// Special case: first part is only a title.
		if (count($names) == 2 && self::isTitleOnly(trim($names[0]))) {
			$titleOnly = trim($names[0]);
			$secondPart = trim($names[1]);
			$words = array_filter(explode(' ', $secondPart));
			
			// Handle "Mr and Mrs Smith" case - where second part is just "Mrs Smith"
			if (count($words) == 2 && self::isValidTitle(self::standardizeTitle($words[0]))) {
				$title1 = self::standardizeTitle($titleOnly);
				$title2 = self::standardizeTitle($words[0]);
				$lastName = $words[1];
				
				return [
					(new self($title1, null, null, $lastName))->toArray(),
					(new self($title2, null, null, $lastName))->toArray()
				];
			}
			// Handle "Mr & Mrs John Smith" case
			else if (!empty($words)) {
				$title1 = self::standardizeTitle($titleOnly);
				$title2 = self::standardizeTitle(array_shift($words));
				$firstName = array_shift($words);
				$lastName = count($words) ? array_pop($words) : null;
				if (!$lastName) {
					throw new \Exception("Unable to determine last name in special title-only case");
				}
				return [
					(new self($title1, $firstName, null, $lastName))->toArray(),
					(new self($title2, null, null, $lastName))->toArray()
				];
			}
		}
		
		$commonLastName = self::getCommonLastName($names);
		return array_map(function($name) use ($commonLastName) {
			return self::parseSingleName($name, $commonLastName)->toArray();
		}, $names);
	}
	
	/**
	 * Checks if the provided string is just a title.
	 *
	 * @param string $name
	 * @return bool
	 */
	private static function isTitleOnly(string $name): bool {
		return in_array(rtrim($name, '.'), ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Mister', 'Master', 'Sir']);
	}
	
	/**
	 * Extracts the common last name from multiple name strings.
	 *
	 * @param array $names Array of name strings.
	 * @return string The common last name.
	 * @throws \Exception If unable to determine a common last name.
	 */
	private static function getCommonLastName(array $names): string {
		// Handle the test case where we're just given titles
		if (count($names) == 2 && self::isTitleOnly(trim($names[0])) && self::isTitleOnly(trim($names[1]))) {
			throw new \Exception("Unable to determine common last name");
		}
		
		// Special case for "Mr and Mrs Smith" format
		if (count($names) == 2) {
			$firstPart = trim($names[0]);
			$secondPart = trim($names[1]);
			
			// If first part is just a title and second part has only one word
			if (self::isTitleOnly($firstPart)) {
				$secondPartWords = explode(' ', $secondPart);
				if (count($secondPartWords) == 1) {
					return $secondPart; // The second part is the last name
				}
			}
		}
		
		// Normal case - find the last name from multi-word parts
		foreach (array_reverse($names) as $name) {
			$parts = array_filter(explode(' ', trim($name)));
			if (count($parts) > 1) {
				return array_pop($parts);
			}
		}
		
		throw new \Exception("Unable to determine common last name");
	}
	
	/**
	 * Parses an individual name string into a Person object.
	 *
	 * Uses a brute-force approach by splitting on spaces and applying heuristics. Should definitely be optimised, maybe with regex
	 * 
	 * @param string $nameString The individual name string.
	 * @param string|null $defaultLastName Optional common last name.
	 * @return Person
	 * @throws \Exception If the name cannot be parsed.
	 */
	private static function parseSingleName(string $nameString, ?string $defaultLastName = null): Person {
		$parts = array_filter(explode(' ', trim($nameString)));
		if (empty($parts)) {
			throw new \Exception("Empty name string provided");
		}
	
		// The first token should be the title.
		$title = self::standardizeTitle(array_shift($parts));
		self::validateTitle($title);
	
		$firstName = null;
		$initials = null;
		$lastName = $defaultLastName;
	
		// If only one token remains, treat it as the last name.
		if (count($parts) === 1) {
			$lastName = array_shift($parts);
		} else {
			// Check the next token: if it is more than one character, assume it's a first name.
			$nextToken = reset($parts);
			if (strlen($nextToken) > 1 && strpos($nextToken, '.') === false) {
				$firstName = array_shift($parts);
			}
			// Process any initials: tokens that are one letter (or one letter with a dot).
			$possibleInitials = [];
			while (!empty($parts) && (strlen(reset($parts)) === 1 || (strlen(reset($parts)) === 2 && substr(reset($parts), -1) === '.'))) {
				$possibleInitials[] = rtrim(array_shift($parts), '.');
			}
			if (!empty($possibleInitials)) {
				$initials = implode(", ", $possibleInitials);
			}
			// The last token is assumed to be the last name.
			if (!empty($parts)) {
				$lastName = array_pop($parts);
			}
		}
	
		if (!$lastName) {
			throw new \Exception("Unable to determine last name for: {$nameString}");
		}
	
		return new self($title, $firstName, $initials, $lastName);
	}
	
	/**
	 * Standardizes a title string (e.g., converts 'Mister' to 'Mr').
	 *
	 * @param string $title
	 * @return string
	 */
	private static function standardizeTitle(string $title): string {
		$cleanTitle = rtrim($title, '.');
		return ($cleanTitle === 'Mister' || $cleanTitle === 'Master') ? 'Mr' : $cleanTitle;
	}
	
	/**
	 * Validates the title against a list of allowed titles.
	 *
	 * @param string $title
	 * @throws \Exception If the title is invalid.
	 */
	public static function validateTitle(string $title): void {
		if (!self::isValidTitle($title)) {
			throw new \Exception("Invalid title detected: {$title}");
		}
	}
	
	/**
	 * Checks if a title is valid without throwing an exception.
	 *
	 * @param string $title The title to validate.
	 * @return bool True if the title is valid, false otherwise.
	 */
	private static function isValidTitle(string $title): bool {
		return in_array($title, ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Sir']);
	}
}