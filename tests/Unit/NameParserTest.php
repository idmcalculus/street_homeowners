<?php

namespace Tests\Unit;

use App\Exceptions\NameParsingException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NameParserTest extends TestCase
{
    #[DataProvider('parseNameStringProvider')]
    public function testParseNameString(string $nameString, array $expected): void
    {
        $result = $this->parser->parse($nameString);
        $this->assertCount(count($expected), $result);
        foreach ($expected as $index => $expectedPerson) {
            $this->assertEquals($expectedPerson, $result[$index]);
        }
    }

    public static function parseNameStringProvider(): \Generator
    {
        yield 'Single name' => [
            'Mr Smith',
            [
                [
                    'title' => 'Mr',
                    'firstName' => null,
                    'initials' => null,
                    'lastName' => 'Smith'
                ]
            ]
        ];

        yield 'Multiple names' => [
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
    }

    public function testEmptyInput(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(4);
        $this->parser->parse('   ');
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::INVALID_FORMAT);
        $this->parser->parse('Invalid format');
    }

    public function testInvalidTitle(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(4);
        $this->parser->parse('Invalid Smith');
    }

    public function testComplexName(): void
    {
        $result = $this->parser->parse('Prof John A. B. Smith');
        $expected = [
            [
                'title' => 'Prof',
                'firstName' => 'John',
                'initials' => 'A, B',
                'lastName' => 'Smith'
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testNameWithoutFirstName(): void
    {
        $result = $this->parser->parse('Dr J. K. Smith');
        $expected = [
            [
                'title' => 'Dr',
                'firstName' => null,
                'initials' => 'J, K',
                'lastName' => 'Smith'
            ]
        ];
        $this->assertEquals($expected, $result);
    }
} 