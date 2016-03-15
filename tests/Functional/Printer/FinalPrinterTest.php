<?php

namespace Tests\Functional\Printer;


use Paraunit\Lifecycle\EngineEvent;
use Paraunit\Parser\JSONLogParser;
use Paraunit\Parser\OutputContainerBearerInterface;
use Paraunit\Printer\FinalPrinter;
use Paraunit\TestResult\NullTestResult;
use Paraunit\TestResult\TestResultContainer;
use Tests\BaseFunctionalTestCase;
use Tests\Stub\StubbedParaunitProcess;
use Tests\Stub\UnformattedOutputStub;

/**
 * Class FinalPrinterTest
 * @package Tests\Functional\Printer
 */
class FinalPrinterTest extends BaseFunctionalTestCase
{
    public function testOnEngineEndPrintsInTheRightOrder()
    {
        $this->markTestIncomplete('need a stub with all the possible outcomes..');
        $output = new UnformattedOutputStub();
        $process = new StubbedParaunitProcess();
        $context = array(
            'start' => new \DateTime('-1 minute'),
            'end' => new \DateTime(),
            'process_completed' => array($process),
        );
        $engineEvent = new EngineEvent($output, $context);

        /** @var JSONLogParser $logParser */
        $logParser = $this->container->get('paraunit.parser.json_log_parser');

        $logParser->getAbnormalTerminatedTestResultContainer()->addToOutputBuffer($process, 'Test');
        foreach ($logParser->getParsersForPrinting() as $parser) {
            if ($parser instanceof OutputContainerBearerInterface) {
                $parser->getTestResultContainer()->addToOutputBuffer($process, 'Test');
            }
        }

        /** @var FinalPrinter $printer */
        $printer = $this->container->get('paraunit.printer.final_printer');

        $printer->onEngineEnd($engineEvent);

        $this->assertNotEmpty($output->getOutput());
        $this->assertOutputOrder($output, array(
            'Unknown',
            'Abnormal Terminations (fatal Errors, Segfaults) output:',
            'Errors output:',
            'Failures output:',
            'Warnings output:',
            'Risky Outcome output:',
            'Skipped Outcome output:',
            'Incomplete Outcome output:',
            'files with UNKNOWN',
            'files with ERRORS',
            'files with FAILURES',
            'files with WARNING',
            'files with RISKY',
            'files with SKIP',
            'files with INCOMPLETE'
        ));
    }

    private function assertOutputOrder(UnformattedOutputStub $output, array $strings)
    {
        $previousPosition = 0;
        $previousString = '<beginning of output>';
        foreach ($strings as $string) {
            $position = strpos($output->getOutput(), $string);
            $this->assertNotSame(false, $position, 'String not found: ' . $string . $output->getOutput());
            $this->assertGreaterThan(
                $previousPosition,
                $position,
                'Failed asserting that "' . $string . '" comes before "' . $previousString . '"'
            );
            $previousString = $string;
            $previousPosition = $position;
        }
    }
}