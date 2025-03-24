<?php

namespace Tests\Unit\Strategies;

use App\Exceptions\NameParsingException;
use App\Strategies\MultipleNameStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MultipleNameStrategyTest extends TestCase
{
    private MultipleNameStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new MultipleNameStrategy();
    }

    #[DataProvider('canParseProvider')]
    public function testCanParse(string $nameString, bool $expected): void
    {
        $this->assertEquals($expected, $this->strategy->canParse($nameString));
    }

    public static function canParseProvider(): \Generator
    {
        yield 'Multiple names with and' => ['Mr and Mrs Smith', true];
        yield 'Multiple names with &' => ['Mr & Mrs Smith', true];
        yield 'Single name' => ['Mr Smith', false];
        yield 'Single name with first name' => ['Mr John Smith', false];
    }

    #[DataProvider('parseProvider')]
    public function testParse(string $nameString, array $expected): void
    {
        $result = $this->strategy->parse($nameString);
        $this->assertCount(count($expected), $result);
        foreach ($expected as $index => $expectedPerson) {
            $this->assertEquals($expectedPerson, $result[$index]);
        }
    }

    public static function parseProvider(): \Generator
    {
        yield 'Mr and Mrs Smith' => [
            'Mr and Mrs Smith',
            [
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

        yield 'Mr & Mrs John Smith' => [
            'Mr & Mrs John Smith',
            [
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

        yield 'Dr John A. Smith and Prof Jane B. Smith' => [
            'Dr John A. Smith and Prof Jane B. Smith',
            [
                [
                    'title' => 'Dr',
                    'firstName' => 'John',
                    'initials' => 'A',
                    'lastName' => 'Smith'
                ],
                [
                    'title' => 'Prof',
                    'firstName' => 'Jane',
                    'initials' => 'B',
                    'lastName' => 'Smith'
                ]
            ]
        ];
    }

    public function testEmptyInput(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(3);
        $this->strategy->parse('   ');
    }

    public function testInvalidTitle(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::INVALID_TITLE);
        $this->strategy->parse('Invalid and Mrs Smith');
    }

    public function testMissingLastName(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::MISSING_LAST_NAME);
        $this->strategy->parse('Mr and Mrs');
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::INVALID_FORMAT);
        $this->strategy->parse('Mr and and Mrs');
    }

    public function testSpecialCaseWithTitleOnly(): void
    {
        $result = $this->strategy->parse('Mr and Mrs Smith');
        $expected = [
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
        ];
        $this->assertEquals($expected, $result);
    }

    public function testComplexMultipleNames(): void
    {
        $result = $this->strategy->parse('Dr John A. Smith and Prof Jane B. Smith');
        $expected = [
            [
                'title' => 'Dr',
                'firstName' => 'John',
                'initials' => 'A',
                'lastName' => 'Smith'
            ],
            [
                'title' => 'Prof',
                'firstName' => 'Jane',
                'initials' => 'B',
                'lastName' => 'Smith'
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testMixedNameFormats(): void
    {
        $result = $this->strategy->parse('Mr John Smith & Mrs Smith');
        $expected = [
            [
                'title' => 'Mr',
                'firstName' => 'John',
                'initials' => null,
                'lastName' => 'Smith'
            ],
            [
                'title' => 'Mrs',
                'firstName' => 'Smith',
                'initials' => null,
                'lastName' => 'Smith'
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testInvalidTitleInSecondName(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::INVALID_TITLE);
        $this->strategy->parse('Mr Smith and Invalid Jones');
    }
} 