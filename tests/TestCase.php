<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\NameParser;
use App\Services\NameParserFactory;

abstract class TestCase extends BaseTestCase
{
    protected NameParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = NameParserFactory::create();
    }
} 