<?php

namespace Paraunit\Runner;


use Symfony\Component\Console\Input\InputInterface;

class AbstractRunner
{
    /** @var  string The PHPUnit XML config file full path */
    protected $phpunitConfigFile;

    /** @var bool  */
    protected $debug = false;

    /**
     * @param InputInterface $input
     */
    protected function extractPhpunitConfigFile(InputInterface $input)
    {
        $configParam = $input->getOption('configuration');
        $configurationFile = getcwd().'/'.$configParam;
        if (!file_exists($configurationFile)) {
            throw new \InvalidArgumentException('The configuration parameter is invalid, file not found');
        }

        $this->phpunitConfigFile = realpath($configurationFile);
    }

    protected function extractDebugOption(InputInterface $input)
    {
        $this->debug = $input->getOption('debug');
    }
}
