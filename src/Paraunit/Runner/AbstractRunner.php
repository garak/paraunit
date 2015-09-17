<?php

namespace Paraunit\Runner;


use Paraunit\Lifecycle\EngineEvent;
use Paraunit\Parser\ProcessOutputParser;
use Paraunit\Printer\DebugPrinter;
use Paraunit\Printer\FinalPrinter;
use Paraunit\Printer\ProcessPrinter;
use Paraunit\Printer\SharkPrinter;
use Paraunit\Process\ParaunitProcessAbstract;
use Paraunit\Process\ParaunitProcessInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractRunner
 * @package Paraunit\Runner
 */
abstract class AbstractRunner
{
    // I'm using Paraunit as a vendor package
    const PHPUNIT_RELPATH_FOR_VENDOR = '/../../../../../phpunit/phpunit/phpunit';
    // I'm using Paraunit standalone (developing)
    const PHPUNIT_RELPATH_FOR_STANDALONE = '/../../../vendor/phpunit/phpunit/phpunit';

    /** @var RetryManager */
    protected $retryManager;

    /** @var ProcessOutputParser */
    protected $processOutputParser;

    /** @var SharkPrinter */
    protected $sharkPrinter;

    /** @var ProcessPrinter */
    protected $processPrinter;

    /** @var FinalPrinter */
    protected $finalPrinter;

    /** @var int */
    protected $maxProcessNumber;

    /** @var ParaunitProcessAbstract[] */
    protected $processStack;

    /** @var ParaunitProcessAbstract[] */
    protected $processCompleted;

    /** @var ParaunitProcessAbstract[] */
    protected $processRunning;

    /** @var  string */
    protected $phpunitBin;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  string The PHPUnit XML config file full path */
    protected $phpunitConfigFile;

    /** @var bool */
    protected $debug = false;

    /**
     * @param RetryManager $retryManager
     * @param ProcessOutputParser $processOutputParser
     * @param ProcessPrinter $processPrinter
     * @param FinalPrinter $finalPrinter
     * @param int $maxProcessNumber
     * @param EventDispatcherInterface $eventDispatcher
     * @throws \Exception
     */
    public function __construct(
        RetryManager $retryManager,
        ProcessOutputParser $processOutputParser,
        ProcessPrinter $processPrinter,
        FinalPrinter $finalPrinter,
        $maxProcessNumber = 10,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->retryManager = $retryManager;
        $this->processOutputParser = $processOutputParser;
        $this->processPrinter = $processPrinter;
        $this->finalPrinter = $finalPrinter;
        $this->eventDispatcher = $eventDispatcher;

        $this->maxProcessNumber = $maxProcessNumber;

        $this->processStack = array();
        $this->processCompleted = array();
        $this->processRunning = array();

        if (file_exists(__DIR__.self::PHPUNIT_RELPATH_FOR_VENDOR)) {
            $this->phpunitBin = __DIR__.self::PHPUNIT_RELPATH_FOR_VENDOR;
        } elseif (file_exists(__DIR__.self::PHPUNIT_RELPATH_FOR_STANDALONE)) {
            $this->phpunitBin = __DIR__.self::PHPUNIT_RELPATH_FOR_STANDALONE;
        } else {
            throw new \Exception('Phpunit not found');
        }

        $this->phpunitBin = realpath($this->phpunitBin);
    }

    /**
     * @param array $files
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function run(array $files, InputInterface $input, OutputInterface $output)
    {
        $this->handleOptions($input);
        $this->engineStart($output);

        $start = new \Datetime('now');
        $this->createProcessStackFromFiles($files);

        while (!empty($this->processStack) || !empty($this->processRunning)) {
            $this->runProcess();

            foreach ($this->processRunning as $process) {
                if ($process->isTerminated()) {
                    $this->retryManager->setRetryStatus($process);
                    $this->processOutputParser->evaluateAndSetProcessResult($process);
                    $this->processPrinter->printProcessResult($output, $process);

                    // Completato o reset e stack
                    $this->markProcessCompleted($process);
                }

                usleep(500);
            }
        }

        $end = new \Datetime('now');

        $this->finalPrinter->printFinalResults($output, $this->processCompleted, $start->diff($end));
        $this->engineShutdown($output);

        return $this->getReturnCode();
    }

    /**
     * @param OutputInterface $output
     */
    protected function engineStart(OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(EngineEvent::BEFORE_START, new EngineEvent($output));
    }

    /**
     * @param OutputInterface $output
     */
    protected function engineShutdown(OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(EngineEvent::BEFORE_SHUTDOWN, new EngineEvent($output));
    }

    protected function runProcess()
    {
        if ($this->maxProcessNumber > count($this->processRunning) && !empty($this->processStack)) {
            /** @var ParaunitProcessInterface $process */
            $process = array_pop($this->processStack);
            $process->start();
            $this->processRunning[md5($process->getCommandLine())] = $process;

            if ($this->debug) {
                DebugPrinter::printDebugOutput($process, $this->processRunning);
            }
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
     * @param InputInterface $input
     */
    protected function extractPhpunitConfigFile(InputInterface $input)
    {
        $this->phpunitConfigFile = $this->extractFileRealPathFromInput($input, 'configuration');
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @return string
     */
    protected function extractFileRealPathFromInput(InputInterface $input, $parameterName)
    {
        $configParam = $input->getOption($parameterName);
        $configurationFile = getcwd().'/'.$configParam;
        if (!file_exists($configurationFile)) {
            throw new \InvalidArgumentException("The $parameterName parameter is  NOT valid, file not found");
        }

        return realpath($configurationFile);
    }

    protected function extractDebugOption(InputInterface $input)
    {
        $this->debug = $input->getOption('debug');
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
     * @param string $fileName
     *
     * @return ParaunitProcessInterface
     */
    abstract protected function createProcess($fileName);

    /**
     * @param InputInterface $input
     */
    abstract protected function handleOptions(InputInterface $input);
}
