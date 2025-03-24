<?php

namespace Tests\Unit\Strategies;

use App\Exceptions\NameParsingException;
use App\Strategies\SingleNameStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SingleNameStrategyTest extends TestCase
{
    private SingleNameStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new SingleNameStrategy();
    }

    #[DataProvider('canParseProvider')]
    public function testCanParse(string $nameString, bool $expected): void
    {
        $this->assertEquals($expected, $this->strategy->canParse($nameString));
    }

    public static function canParseProvider(): \Generator
    {
        yield 'Single name' => ['Mr Smith', true];
        yield 'Single name with first name' => ['Mr John Smith', true];
        yield 'Single name with initials' => ['Mr J. K. Smith', true];
        yield 'Multiple names' => ['Mr and Mrs Smith', false];
        yield 'Multiple names with &' => ['Mr & Mrs Smith', false];
    }

    #[DataProvider('parseProvider')]
    public function testParse(string $nameString, array $expected): void
    {
        $result = $this->strategy->parse($nameString);
        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result[0]);
    }

    public static function parseProvider(): \Generator
    {
        yield 'Title and last name' => [
            'Mr Smith',
            [
                'title' => 'Mr',
                'firstName' => null,
                'initials' => null,
                'lastName' => 'Smith'
            ]
        ];

        yield 'Full name' => [
            'Mrs Jane Smith',
            [
                'title' => 'Mrs',
                'firstName' => 'Jane',
                'initials' => null,
                'lastName' => 'Smith'
            ]
        ];

        yield 'Name with initials' => [
            'Dr J. K. Smith',
            [
                'title' => 'Dr',
                'firstName' => null,
                'initials' => 'J, K',
                'lastName' => 'Smith'
            ]
        ];

        yield 'Full name with initials' => [
            'Prof John A. B. Smith',
            [
                'title' => 'Prof',
                'firstName' => 'John',
                'initials' => 'A, B',
                'lastName' => 'Smith'
            ]
        ];
    }

    public function testEmptyInput(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::EMPTY_INPUT);
        $this->strategy->parse('');
    }

    public function testInvalidTitle(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::INVALID_TITLE);
        $this->strategy->parse('Invalid Smith');
    }

    public function testMissingLastName(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::MISSING_LAST_NAME);
        $this->strategy->parse('Mr');
    }
} 