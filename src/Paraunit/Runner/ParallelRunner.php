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
use Paraunit\Process\SymfonyProcessWrapper;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ParallelRunner.
 */
class ParallelRunner extends AbstractRunner implements RunnerInterface
{
    /**
     * @param InputInterface $input
     */
    protected function handleOptions(InputInterface $input)
    {
        $this->extractPhpunitConfigFile($input);
        $this->extractDebugOption($input);
    }

    /**
     * @param string $fileName
     *
     * @return ParaunitProcessInterface
     */
    protected function createProcess($fileName)
    {
        $command =
            $this->phpunitBin .
            ' -c '.$this->phpunitConfigFile . ' ' .
            ' --colors=never ' .
            $fileName .
            ' 2>&1';

        return new SymfonyProcessWrapper($command);
    }
}
