<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Person;

class PersonTest extends TestCase {
	public function testEmptyInput()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Empty name string provided');
		Person::parseNameString("");
	}

	public function testInvalidInput()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Invalid title detected: random');
		Person::parseNameString("random text without titles or names");
	}
  
	#[DataProvider('singlePersonWithInitialsProvider')]
	public function testSinglePersonWithInitials(string $nameString, string $expectedTitle, ?string $expectedFirstName, string $expectedInitials, string $expectedLastName) {
		$people = Person::parseNameString($nameString);
		$this->assertCount(1, $people);
		$this->assertEquals($expectedTitle, $people[0]['title']);
		$this->assertEquals($expectedFirstName, $people[0]['firstName']);
		$this->assertEquals($expectedInitials, $people[0]['initials']);
		$this->assertEquals($expectedLastName, $people[0]['lastName']);
	}
	
	public static function singlePersonWithInitialsProvider(): \Generator
	{
		yield 'Single person with initial and fullstop' => [
			'nameString' => "Mr J. Smith",
			'expectedTitle' => 'Mr',
			'expectedFirstName' => null,
			'expectedInitials' => 'J',
			'expectedLastName' => 'Smith'
		];
		
		yield 'Single person with initial and no fullstop' => [
			'nameString' => "Mr J Smith",
			'expectedTitle' => 'Mr',
			'expectedFirstName' => null,
			'expectedInitials' => 'J',
			'expectedLastName' => 'Smith'
		];
		
		yield 'Single person with multiple initials' => [
			'nameString' => "Mr J. R. R. Smith",
			'expectedTitle' => 'Mr',
			'expectedFirstName' => null,
			'expectedInitials' => 'J, R, R',
			'expectedLastName' => 'Smith'
		];
	}

	public function testSinglePersonWithFullName() {
		$people = Person::parseNameString("Mrs Jane McMaster");
		$this->assertCount(1, $people);
		$this->assertEquals('Mrs', $people[0]['title']);
		$this->assertEquals('Jane', $people[0]['firstName']);
		$this->assertNull($people[0]['initials']);
		$this->assertEquals('McMaster', $people[0]['lastName']);
	}

	#[DataProvider('multiplePeopleProvider')]
	public function testMultiplePeople(string $nameString, array $expectedPeople) {
		$people = Person::parseNameString($nameString);
		$this->assertCount(count($expectedPeople), $people);
		
		foreach ($expectedPeople as $index => $expected) {
			$this->assertEquals($expected['title'], $people[$index]['title']);
			$this->assertEquals($expected['firstName'], $people[$index]['firstName']);
			$this->assertEquals($expected['initials'], $people[$index]['initials']);
			$this->assertEquals($expected['lastName'], $people[$index]['lastName']);
		}
	}
	
	public static function multiplePeopleProvider(): \Generator
	{
		yield 'Multiple people with and' => [
			'nameString' => "Mr and Mrs Smith",
			'expectedPeople' => [
				[
					'title' => 'Mr',
					'firstName' => null,
					'initials' => null,
					'lastName' => 'Smith'
				],
				[
					'title' => 'Mrs',
					'firstName' => null,
					'initials' => null,
					'lastName' => 'Smith'
				]
			]
		];
		
		yield 'Multiple people with ampersand' => [
			'nameString' => "Dr & Mrs Joe Bloggs",
			'expectedPeople' => [
				[
					'title' => 'Dr',
					'firstName' => 'Joe',
					'initials' => null,
					'lastName' => 'Bloggs'
				],
				[
					'title' => 'Mrs',
					'firstName' => null,
					'initials' => null,
					'lastName' => 'Bloggs'
				]
			]
		];
		
		yield 'Multiple people with and and full names' => [
			'nameString' => "Mr Tom Staff and Mr John Doe",
			'expectedPeople' => [
				[
					'title' => 'Mr',
					'firstName' => 'Tom',
					'initials' => null,
					'lastName' => 'Staff'
				],
				[
					'title' => 'Mr',
					'firstName' => 'John',
					'initials' => null,
					'lastName' => 'Doe'
				]
			]
		];
		
		yield 'Multiple people with different last names' => [
			'nameString' => "Prof. John F. Smith and Dr. Jane A. B. Doe",
			'expectedPeople' => [
				[
					'title' => 'Prof',
					'firstName' => 'John',
					'initials' => 'F',
					'lastName' => 'Smith'
				],
				[
					'title' => 'Dr',
					'firstName' => 'Jane',
					'initials' => 'A, B',
					'lastName' => 'Doe'
				]
			]
		];
		
		yield 'Special case for title and title' => [
			'nameString' => "Mr & Mrs John Smith",
			'expectedPeople' => [
				[
					'title' => 'Mr',
					'firstName' => 'John',
					'initials' => null,
					'lastName' => 'Smith'
				],
				[
					'title' => 'Mrs',
					'firstName' => null,
					'initials' => null,
					'lastName' => 'Smith'
				]
			]
		];
	}

	public function testSinglePersonWithFullNameAndInitial(): void
	{
		$people = Person::parseNameString("Mr John A. Smith");
		$this->assertCount(1, $people);
		$this->assertEquals("Mr", $people[0]['title']);
		$this->assertEquals("John", $people[0]['firstName']);
		$this->assertEquals("A", $people[0]['initials']);
		$this->assertEquals("Smith", $people[0]['lastName']);
	}

	// Additional tests for better coverage
	
	public function testPersonToArray(): void
	{
		$person = new Person('Mr', 'John', 'A', 'Smith');
		$array = $person->toArray();
		
		$this->assertIsArray($array);
		$this->assertEquals('Mr', $array['title']);
		$this->assertEquals('John', $array['firstName']);
		$this->assertEquals('A', $array['initials']);
		$this->assertEquals('Smith', $array['lastName']);
	}
	
	#[DataProvider('standardizeTitleProvider')]
	public function testStandardizeTitle(string $input, string $expected): void
	{
		// Test the standardizeTitle method through reflection
		$class = new \ReflectionClass(Person::class);
		$method = $class->getMethod('standardizeTitle');
		$method->setAccessible(true);
		
		$this->assertEquals($expected, $method->invoke(null, $input));
	}
	
	public static function standardizeTitleProvider(): \Generator
	{
		yield 'Mr stays as Mr' => ['Mr', 'Mr'];
		yield 'Mr. becomes Mr' => ['Mr.', 'Mr'];
		yield 'Mrs stays as Mrs' => ['Mrs', 'Mrs'];
		yield 'Mrs. becomes Mrs' => ['Mrs.', 'Mrs'];
		yield 'Mister becomes Mr' => ['Mister', 'Mr'];
		yield 'Master becomes Mr' => ['Master', 'Mr'];
		yield 'Dr stays as Dr' => ['Dr', 'Dr'];
		yield 'Dr. becomes Dr' => ['Dr.', 'Dr'];
		yield 'Prof stays as Prof' => ['Prof', 'Prof'];
		yield 'Prof. becomes Prof' => ['Prof.', 'Prof'];
	}
	
	#[DataProvider('titleValidationProvider')]
	public function testValidateTitleMethod(string $title, bool $expected): void
	{
		if ($expected) {
			// Valid titles should not throw an exception
			Person::validateTitle($title);
			$this->assertTrue(true);
		} else {
			$this->expectException(\Exception::class);
			Person::validateTitle($title);
		}
	}
	
	public static function titleValidationProvider(): \Generator
	{
		yield 'Mr is valid' => ['Mr', true];
		yield 'Mrs is valid' => ['Mrs', true];
		yield 'Ms is valid' => ['Ms', true];
		yield 'Dr is valid' => ['Dr', true];
		yield 'Prof is valid' => ['Prof', true];
		yield 'Sir is valid' => ['Sir', true];
		yield 'Revd is invalid' => ['Revd', false];
		yield 'Master is invalid' => ['Master', false];
		yield 'Mister is invalid' => ['Mister', false];
		yield 'Invalid is invalid' => ['Invalid', false];
	}
	
	#[DataProvider('exceptionMessagesProvider')]
	public function testExceptionMessages(string $nameString, string $expectedExceptionMessage): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage($expectedExceptionMessage);
		
		Person::parseNameString($nameString);
	}
	
	public static function exceptionMessagesProvider(): \Generator
	{
		yield 'Empty input' => ['', 'Empty name string provided'];
		yield 'Invalid input' => ['random text without titles or names', 'Invalid title detected: random'];
		yield 'Invalid format' => ['Invalid format', 'Invalid title detected: Invalid'];
	}
	
	public function testUnableToDetermineCommonLastName(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Unable to determine common last name');
		// This is a contrived example to test the exception
		// We're using reflection to call the protected method directly
		$class = new \ReflectionClass(Person::class);
		$method = $class->getMethod('getCommonLastName');
		$method->setAccessible(true);
		$method->invoke(null, ['Mr', 'Mrs']);
	}
	
	public function testGetCommonLastNameSpecialCase(): void
	{
		// Test the special case where first part is just a title and second part has only one word
		$class = new \ReflectionClass(Person::class);
		$method = $class->getMethod('getCommonLastName');
		$method->setAccessible(true);
		
		$result = $method->invoke(null, ['Mr', 'Smith']);
		$this->assertEquals('Smith', $result);
	}
	
	public function testGetCommonLastNameNormalCase(): void
	{
		// Test the normal case where we find the last name from multi-word parts
		$class = new \ReflectionClass(Person::class);
		$method = $class->getMethod('getCommonLastName');
		$method->setAccessible(true);
		
		$result = $method->invoke(null, ['Mr John Smith', 'Mrs Jane Smith']);
		$this->assertEquals('Smith', $result);
	}
	
	public function testTestInvalidTitle(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Invalid title detected: InvalidTitle');
		
		Person::validateTitle('InvalidTitle');
	}
	
	#[DataProvider('isValidTitleProvider')]
	public function testIsValidTitle(string $title, bool $expected): void
	{
		// Test the isValidTitle method through reflection
		$class = new \ReflectionClass(Person::class);
		$method = $class->getMethod('isValidTitle');
		$method->setAccessible(true);
		
		$this->assertEquals($expected, $method->invoke(null, $title));
	}
	
	public static function isValidTitleProvider(): \Generator
	{
		yield 'Mr is valid' => ['Mr', true];
		yield 'Mrs is valid' => ['Mrs', true];
		yield 'Ms is valid' => ['Ms', true];
		yield 'Dr is valid' => ['Dr', true];
		yield 'Prof is valid' => ['Prof', true];
		yield 'Sir is valid' => ['Sir', true];
		yield 'Revd is invalid' => ['Revd', false];
		yield 'Master is invalid' => ['Master', false];
		yield 'Mister is invalid' => ['Mister', false];
		yield 'Invalid is invalid' => ['Invalid', false];
	}
	
	public function testParseSingleNameWithInitials(): void
	{
		// Test parsing a name with multiple initials
		$people = Person::parseNameString("Mr J. K. Smith");
		$this->assertCount(1, $people);
		$this->assertEquals('Mr', $people[0]['title']);
		$this->assertNull($people[0]['firstName']);
		$this->assertEquals('J, K', $people[0]['initials']);
		$this->assertEquals('Smith', $people[0]['lastName']);
		
		// Test parsing a name with a first name and initials
		$people = Person::parseNameString("Dr John A. B. Doe");
		$this->assertCount(1, $people);
		$this->assertEquals('Dr', $people[0]['title']);
		$this->assertEquals('John', $people[0]['firstName']);
		$this->assertEquals('A, B', $people[0]['initials']);
		$this->assertEquals('Doe', $people[0]['lastName']);
	}
}