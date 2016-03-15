<?php

namespace Paraunit\Process;

use Paraunit\TestResult\PrintableTestResultInterface;

/**
 * Class AbstractParaunitProcess
 * @package Paraunit\Process
 */
abstract class AbstractParaunitProcess implements ParaunitProcessInterface, RetryAwareInterface, ProcessWithResultsInterface
{
    /** @var int */
    protected $retryCount = 0;

    /** @var bool */
    protected $shouldBeRetried = false;

    /** @var string */
    protected $uniqueId;

    /** @var string */
    protected $filename;

    /** @var PrintableTestResultInterface[] */
    protected $testResults;

    /** @var  bool */
    private $waitingForTestResult;

    /**
     * {@inheritdoc}
     */
    public function __construct($commandLine, $uniqueId)
    {
        $this->uniqueId = $uniqueId;

        $filename = array();
        if (preg_match('/[A-z]*\.php/', $commandLine, $filename) === 1) {
            $this->filename = $filename[0];
        }

        $this->testResults = array();
        $this->waitingForTestResult = false;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return int
     */
    public function getRetryCount()
    {
        return $this->retryCount;
    }

    /**
     */
    public function increaseRetryCount()
    {
        ++$this->retryCount;
    }

    public function markAsToBeRetried()
    {
        $this->shouldBeRetried = true;
        $this->testResults = array();
    }

    /**
     * @return bool
     */
    public function isToBeRetried()
    {
        return $this->shouldBeRetried;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->shouldBeRetried = false;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return PrintableTestResultInterface[]
     */
    public function getTestResults()
    {
        return $this->testResults;
    }

    /**
     * @param PrintableTestResultInterface $testResult
     */
    public function addTestResult(PrintableTestResultInterface $testResult)
    {
        $this->testResults[] = $testResult;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAbnormalTermination()
    {
        // TODO -- cambiare
    }

    /**
     * @return bool
     */
    public function isWaitingForTestResult()
    {
        return $this->waitingForTestResult;
    }

    /**
     * @param boolean $waitingForTestResult
     */
    public function setWaitingForTestResult($waitingForTestResult)
    {
        $this->waitingForTestResult = (bool) $waitingForTestResult;
    }
}