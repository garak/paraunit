<?php

namespace Paraunit\Runner;


use Paraunit\Lifecycle\CoverageEvent;
use Paraunit\Process\ParaunitProcessInterface;
use Paraunit\Process\SymfonyProcessWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CoverageRunner extends AbstractRunner implements RunnerInterface
{
    /** @var  string */
    protected $tempDir;

    /** @var  string */
    protected $coverageCloverFile;

    /** @var  string */
    protected $coverageXmlFile;

    /** @var  string */
    protected $coverageHtmlFile;

    /**
     * @param InputInterface $input
     */
    protected function handleOptions(InputInterface $input)
    {
        $this->extractPhpunitConfigFile($input);
        $this->extractCoverageFiles($input);
        $this->extractDebugOption($input);

        $this->tempDir = sys_get_temp_dir() . '/paraunit/';
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
            ' --coverage-php=' . $this->generateCoverageFilename($fileName) . ' ' .
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
     * @param InputInterface $input
     */
    protected function extractCoverageFiles(InputInterface $input)
    {
        $this->coverageCloverFile = $this->extractFileRealPathFromInput($input, 'output-clover');
        $this->coverageXmlFileFile = $this->extractFileRealPathFromInput($input, 'output-xml');
        $this->coverageHtmlFile = $this->extractFileRealPathFromInput($input, 'output-html');
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function generateCoverageFilename($fileName)
    {
        return tempnam($this->tempDir, pathinfo($fileName, PATHINFO_FILENAME));
    }
}
