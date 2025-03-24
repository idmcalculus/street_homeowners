<?php

namespace Tests\Unit\Services;

use App\NameParser;
use App\Services\NameParserFactory;
use App\Strategies\SingleNameStrategy;
use PHPUnit\Framework\TestCase;

class NameParserFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $parser = NameParserFactory::create();
        $this->assertInstanceOf(NameParser::class, $parser);
    }

    public function testCreateWithStrategies(): void
    {
        $strategies = [new SingleNameStrategy()];
        $parser = NameParserFactory::createWithStrategies($strategies);
        $this->assertInstanceOf(NameParser::class, $parser);
        
        // Test with custom strategy
        $result = $parser->parse('Mr Smith');
        $expected = [
            [
                'title' => 'Mr',
                'firstName' => null,
                'initials' => null,
                'lastName' => 'Smith'
            ]
        ];
        $this->assertEquals($expected, $result);
    }
} 