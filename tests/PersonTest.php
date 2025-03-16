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
}