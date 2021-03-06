<?php

namespace Paraunit\Process;

use Paraunit\TestResult\Interfaces\PrintableTestResultInterface;
use Paraunit\TestResult\Interfaces\TestResultContainerInterface;

/**
 * Interface RetryAwareInterface.
 */
interface ProcessWithResultsInterface extends TestResultContainerInterface
{
    /**
     * @param PrintableTestResultInterface $testResult
     */
    public function addTestResult(PrintableTestResultInterface $testResult);

    /** @return bool */
    public function hasAbnormalTermination();

    /** @return bool */
    public function isToBeRetried();

    /** @return string */
    public function getFilename();

    /** @return bool */
    public function isWaitingForTestResult();

    /**
     * @param boolean $waitingForTestResult
     */
    public function setWaitingForTestResult($waitingForTestResult);
}
