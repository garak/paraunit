<?php

namespace Paraunit\Runner;


use Paraunit\Lifecycle\CoverageEvent;
use Paraunit\Process\ParaunitProcessInterface;
use Paraunit\Process\SymfonyProcessWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CoverageRunner extends AbstractRunner implements RunnerInterface
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
            ' --coverage-php=' . $this->generateCoverageFilename() .
            $fileName .
            ' 2>&1';

        return new SymfonyProcessWrapper($command);
    }

    /**
     * @param OutputInterface $output
     */
    protected function engineStart(OutputInterface $output)
    {
        parent::engineStart($output);
        $this->eventDispatcher->dispatch(CoverageEvent::COVERAGE_START, new CoverageEvent());
    }

    /**
     * @param OutputInterface $output
     */
    protected function engineShutdown(OutputInterface $output)
    {
        parent::engineShutdown($output);
        $this->eventDispatcher->dispatch(CoverageEvent::COVERAGE_END, new CoverageEvent());
    }

    /**
     * @return string
     */
    private function generateCoverageFilename()
    {
        // TODO -- filename per i file php di coverage da generare
    }
}
