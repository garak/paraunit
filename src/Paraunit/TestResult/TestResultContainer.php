<?php

namespace Paraunit\TestResult;
use Paraunit\Parser\JSONParserChainElementInterface;
use Paraunit\Process\ProcessWithResultsInterface;

/**
 * Class TestResultContainer
 * @package Paraunit\TestResult
 */
class TestResultContainer implements TestResultContainerInterface, JSONParserChainElementInterface
{
    /** @var JSONParserChainElementInterface */
    private $parser;

    /** @var  TestResultFormat */
    private $testResultFormat;

    /** @var  PrintableTestResultInterface[] */
    private $testResults;

    /** @var  string[] */
    private $filenames;

    /**
     * TestResultContainer constructor.
     * @param JSONParserChainElementInterface $parser
     * @param TestResultFormat $testResultFormat
     */
    public function __construct(JSONParserChainElementInterface $parser, TestResultFormat $testResultFormat)
    {
        $this->parser = $parser;
        $this->testResultFormat = $testResultFormat;
        $this->filenames = array();
    }

    /**
     * @param ProcessWithResultsInterface $process
     * @param \stdClass $logItem
     * @return null|TestResultInterface|PrintableTestResultInterface Returned when the chain needs to stop
     */
    public function handleLogItem(ProcessWithResultsInterface $process, \stdClass $logItem)
    {
        $result = $this->parser->handleLogItem($process, $logItem);

        if ($result instanceof PrintableTestResultInterface) {
            $this->addTestResult($process, $result);
            $process->addTestResult($result);
        }

        return $result;
    }

    /**
     * @return TestResultFormat
     */
    public function getTestResultFormat()
    {
        return $this->testResultFormat;
    }

    /**
     * @return PrintableTestResultInterface[]
     */
    public function getTestResults()
    {
        return $this->testResults;
    }

    /**
     * @param ProcessWithResultsInterface $process
     * @param PrintableTestResultInterface $testResult
     */
    protected function addTestResult(ProcessWithResultsInterface $process, PrintableTestResultInterface $testResult)
    {
        $this->testResults[] = $testResult;
        // trick for unique
        $this->filenames[$process->getFilename()] = $process->getFilename();
    }

    /**
     * @return int
     * @todo test
     */
    public function countFilenames()
    {
        return count($this->filenames);
    }

    /**
     * @return int
     */
    public function countTestResults()
    {
        return count($this->testResults);
    }

    /**
     * @return string[]
     */
    public function getFileNames()
    {
        return $this->filenames;
    }
}