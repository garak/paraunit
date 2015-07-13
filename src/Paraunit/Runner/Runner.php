<?php

namespace Paraunit\Runner;

use Paraunit\Parser\ProcessOutputParser;
use Paraunit\Printer\FinalPrinter;
use Paraunit\Printer\ProcessPrinter;
use Paraunit\Printer\SharkPrinter;
use Paraunit\Process\ParaunitProcessAbstract;
use Paraunit\Process\ParaunitProcessInterface;
use Paraunit\Process\SymfonyProcessWrapper;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Runner
 * @package Paraunit\Runner
 */
class Runner
{
    /**
     * @var RetryManager
     */
    protected $retryManager;

    /**
     * @var ProcessOutputParser
     */
    protected $processOutputParser;

    /**
     * @var SharkPrinter
     */
    protected $sharkPrinter;

    /**
     * @var ProcessPrinter
     */
    protected $processPrinter;

    /**
     * @var FinalPrinter
     */
    protected $finalPrinter;

    /**
     * @var int
     */
    protected $maxProcessNumber;

    /**
     * @var ParaunitProcessAbstract[]
     */
    protected $processStack;

    /**
     * @var ParaunitProcessAbstract[]
     */
    protected $processCompleted;

    /**
     * @var ParaunitProcessAbstract[]
     */
    protected $processRunning;

    /**
     * @param RetryManager $retryManager
     * @param ProcessOutputParser $processOutputParser
     * @param SharkPrinter $sharkPrinter
     * @param ProcessPrinter $processPrinter
     * @param FinalPrinter $finalPrinter
     * @param int $maxProcessNumber
     */
    function __construct(
        RetryManager $retryManager,
        ProcessOutputParser $processOutputParser,
        SharkPrinter $sharkPrinter,
        ProcessPrinter $processPrinter,
        FinalPrinter $finalPrinter,
        $maxProcessNumber = 10
    )
    {
        $this->retryManager = $retryManager;
        $this->processOutputParser = $processOutputParser;
        $this->sharkPrinter = $sharkPrinter;
        $this->processPrinter = $processPrinter;
        $this->finalPrinter = $finalPrinter;

        $this->maxProcessNumber = $maxProcessNumber;

        $this->processStack = array();
        $this->processCompleted = array();
        $this->processRunning = array();
    }

    /**
     * @param $files
     * @param OutputInterface $outputInterface
     * @return int
     */
    public function run($files, OutputInterface $outputInterface)
    {
        $this->formatOutputInterface($outputInterface);

        $this->sharkPrinter->printSharkLogo($outputInterface);

        $start = new \Datetime('now');
        $this->createProcessStackFromFiles($files);


        while (!empty($this->processStack) || !empty($this->processRunning)) {

            $this->runProcess();

            foreach ($this->processRunning as $process) {
                if ($process->isTerminated()) {
                    $this->retryManager->setRetryStatus($process);
                    $this->processOutputParser->evaluateAndSetProcessResult($process);
                    $this->processPrinter->printProcessResult($outputInterface, $process);

                    // Completato o reset e stack
                    $this->markProcessCompleted($process);
                }

                usleep(500);
            }
        }

        $end = new \Datetime('now');

        $this->finalPrinter->printFinalResults($outputInterface, $this->processCompleted, $start->diff($end));

        return $this->getReturnCode();
    }

    /**
     * @return int
     */
    protected function getReturnCode()
    {
        foreach ($this->processCompleted as $process) {
            if ($process->getExitCode() != 0) {
                return 10;
            }
        }

        return 0;
    }

    /**
     * @param string[] $files
     */
    protected function createProcessStackFromFiles($files)
    {
        foreach ($files as $file) {
            $process = $this->createProcess($file);
            $this->processStack[md5($process->getCommandLine())] = $process;
        }
    }

    /**
     * @param string $fileName
     * @return ParaunitProcessInterface
     */
    protected function createProcess($fileName)
    {
        return new SymfonyProcessWrapper('./bin/phpunit ' . $fileName . ' 2>&1');
    }

    protected function runProcess()
    {
        if ($this->maxProcessNumber > count($this->processRunning) && !empty($this->processStack)) {
            /** @var ParaunitProcessInterface $process */
            $process = array_pop($this->processStack);
            $process->start();
            $this->processRunning[md5($process->getCommandLine())] = $process;
        }
    }

    /**
     * @param ParaunitProcessAbstract $process
     */
    protected function markProcessCompleted(ParaunitProcessAbstract $process)
    {
        $pHash = $process->getUniqueId();
        unset($this->processRunning[$pHash]);

        if ($process->isToBeRetried()) {
            $process->reset();
            $this->processStack[$pHash] = $process;
        } else {
            $this->processCompleted[$pHash] = $process;
        }
    }

    /**
     * @param OutputInterface $outputInterface
     */
    protected function formatOutputInterface(OutputInterface $outputInterface)
    {
        if ($outputInterface->getFormatter()) {

            $style = new OutputFormatterStyle('green', null, array('bold', 'blink'));
            $outputInterface->getFormatter()->setStyle('ok', $style);

            $style = new OutputFormatterStyle('yellow', null, array('bold', 'blink'));
            $outputInterface->getFormatter()->setStyle('skipped', $style);

            $style = new OutputFormatterStyle('blue', null, array('bold', 'blink'));
            $outputInterface->getFormatter()->setStyle('incomplete', $style);

            $style = new OutputFormatterStyle('red', null, array('bold', 'blink'));
            $outputInterface->getFormatter()->setStyle('fail', $style);

            $style = new OutputFormatterStyle('red', null, array('bold', 'blink'));
            $outputInterface->getFormatter()->setStyle('error', $style);
        }
    }
}
