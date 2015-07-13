<?php

namespace Paraunit\Tests\Unit;

use Paraunit\Parser\TestResultsParser;
use Paraunit\Tests\Stub\StubbedParaProcess;
use Paraunit\Tests\StubbedPHPUnitBaseTestCase;

class TestResultsParserTest extends StubbedPHPUnitBaseTestCase
{
    /**
     * @dataProvider processProvider
     */
    public function testParseAndContinue(StubbedParaProcess $process, $expectedResult)
    {
        $parser = new TestResultsParser();

        $this->assertTrue($parser->parseAndContinue($process));
        $this->assertEquals($expectedResult, $process->getTestResults());
    }

    public function processProvider()
    {
        return [
            [$this->getTestWithSingleError(), str_split('....E....')],
            [$this->getTestWith2Errors2Failures(), str_split('FF..E...E')],
            [$this->getTestWithAllGreen(), str_split('.........')],
            [$this->getTestWithFatalError(), []],
        ];
    }

    public function testParseAndContinueWithVeryLongOutput()
    {
        $process = $this->getTestWithVeryLongOutput();

        $parser = new TestResultsParser();

        $this->assertTrue($parser->parseAndContinue($process));
        $this->assertEquals(str_repeat('.', 77), implode($process->getTestResults()));
    }
}