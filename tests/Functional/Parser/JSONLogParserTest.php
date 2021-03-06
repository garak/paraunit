<?php

namespace Tests\Functional\Parser;

use Paraunit\Configuration\JSONLogFilename;
use Paraunit\Lifecycle\ProcessEvent;
use Paraunit\Parser\JSONLogParser;
use Paraunit\TestResult\Interfaces\PrintableTestResultInterface;
use Paraunit\TestResult\TestResultContainer;
use Paraunit\TestResult\TestResultWithMessage;
use Tests\BaseFunctionalTestCase;
use Tests\Stub\PHPUnitJSONLogOutput\JSONLogStub;
use Tests\Stub\StubbedParaunitProcess;

/**
 * Class JSONLogParserTest
 * @package Tests\Functional\Parser
 */
class JSONLogParserTest extends BaseFunctionalTestCase
{
    /**
     * @dataProvider parsableResultsProvider
     */
    public function testParse($stubLog, $expectedResult, $hasAbnormalTermination = false)
    {
        $process = new StubbedParaunitProcess();
        $this->createLogForProcessFromStubbedLog($process, $stubLog);

        /** @var JSONLogParser $parser */
        $parser = $this->container->get('paraunit.parser.json_log_parser');

        $parser->onProcessTerminated(new ProcessEvent($process));

        $results = $process->getTestResults();
        $this->assertContainsOnlyInstancesOf('Paraunit\TestResult\Interfaces\PrintableTestResultInterface', $results);
        $textResults = '';
        /** @var PrintableTestResultInterface $singleResult */
        foreach ($results as $singleResult) {
            $textResults .= $singleResult->getTestResultFormat()->getTestResultSymbol();
        }
        $this->assertEquals($expectedResult, $textResults);

        $this->assertEquals($hasAbnormalTermination, $process->hasAbnormalTermination());
    }

    public function parsableResultsProvider()
    {
        return array(
            array(JSONLogStub::TWO_ERRORS_TWO_FAILURES, 'FF..E...E'),
            array(JSONLogStub::ALL_GREEN, '.........'),
            array(JSONLogStub::ONE_ERROR, '.E.'),
            array(JSONLogStub::ONE_INCOMPLETE, '..I.'),
            array(JSONLogStub::ONE_RISKY, '..R.'),
            array(JSONLogStub::ONE_SKIP, '..S.'),
            array(JSONLogStub::ONE_WARNING, '...W'),
            array(JSONLogStub::FATAL_ERROR, '...X', true),
            array(JSONLogStub::SEGFAULT, '...X', true),
            array(JSONLogStub::PARSE_ERROR, '...................................................X', true),
            array(JSONLogStub::UNKNOWN, '?', false),
        );
    }

    public function testParseHandlesMissingLogsAsAbnormalTerminations()
    {
        /** @var JSONLogParser $parser */
        $parser = $this->container->get('paraunit.parser.json_log_parser');
        $process = new StubbedParaunitProcess();

        $parser->onProcessTerminated(new ProcessEvent($process));

        $results = $process->getTestResults();
        $this->assertContainsOnlyInstancesOf('Paraunit\TestResult\Interfaces\PrintableTestResultInterface', $results);
        $this->assertCount(1, $results);

        $this->assertEquals('X', $results[0]->getTestResultFormat()->getTestResultSymbol());
        $this->assertTrue($process->hasAbnormalTermination());
    }
}
