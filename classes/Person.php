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
     * Converts the Person instance to an associative array.
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
}