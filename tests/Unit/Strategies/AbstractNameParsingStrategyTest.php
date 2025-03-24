<?php

namespace Tests\Unit\Strategies;

use App\Exceptions\NameParsingException;
use App\Strategies\AbstractNameParsingStrategy;
use PHPUnit\Framework\TestCase;

class AbstractNameParsingStrategyTest extends TestCase
{
    private AbstractNameParsingStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new class extends AbstractNameParsingStrategy {
            public function canParse(string $nameString): bool
            {
                return true;
            }

            public function parse(string $nameString): array
            {
                return [];
            }
        };
    }

    public function testValidateResultWithEmptyArray(): void
    {
        $this->assertTrue($this->strategy->validateResult([]));
    }

    public function testValidateResultWithValidData(): void
    {
        $result = [
            [
                'title' => 'Mr',
                'firstName' => 'John',
                'initials' => null,
                'lastName' => 'Smith'
            ]
        ];
        $this->assertTrue($this->strategy->validateResult($result));
    }

    public function testValidateResultWithInvalidStructure(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::VALIDATION_ERROR);
        $this->strategy->validateResult([['invalid' => 'data']]);
    }

    public function testValidateResultWithInvalidTypes(): void
    {
        $this->expectException(NameParsingException::class);
        $this->expectExceptionCode(NameParsingException::VALIDATION_ERROR);
        $this->strategy->validateResult([
            [
                'title' => 123, // Should be string
                'firstName' => null,
                'initials' => null,
                'lastName' => 'Smith'
            ]
        ]);
    }
} 