<?php

namespace Tests\Unit;

use App\Person;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonTest extends TestCase
{
    #[DataProvider('personDataProvider')]
    public function testPersonInstantiation(string $title, ?string $firstName, ?string $initials, string $lastName): void
    {
        $person = new Person($title, $firstName, $initials, $lastName);
        
        $this->assertInstanceOf(Person::class, $person);
        $this->assertEquals($title, $person->title);
        $this->assertEquals($firstName, $person->firstName);
        $this->assertEquals($initials, $person->initials);
        $this->assertEquals($lastName, $person->lastName);
    }

    public static function personDataProvider(): \Generator
    {
        yield 'Full person data' => ['Mr', 'John', 'A', 'Smith'];
        yield 'Person with null firstName' => ['Mr', null, 'J', 'Smith'];
        yield 'Person with null initials' => ['Mrs', 'Jane', null, 'Smith'];
        yield 'Person with null firstName and initials' => ['Mr', null, null, 'Smith'];
    }

    #[DataProvider('personDataProvider')]
    public function testPersonToArray(string $title, ?string $firstName, ?string $initials, string $lastName): void
    {
        $person = new Person($title, $firstName, $initials, $lastName);
        $array = $person->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals($title, $array['title']);
        $this->assertEquals($firstName, $array['firstName']);
        $this->assertEquals($initials, $array['initials']);
        $this->assertEquals($lastName, $array['lastName']);
    }
} 