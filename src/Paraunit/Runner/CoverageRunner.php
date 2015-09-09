<?php

namespace Paraunit\Runner;


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
            ' --coverage-php=' . $this->generateFilename() .
            $fileName .
            ' 2>&1';

        return new SymfonyProcessWrapper($command);
    }

    /**
     * @return string
     */
    private function generateFilename()
    {
        // TODO -- filename per i file php di coverage da generare
    }
}
