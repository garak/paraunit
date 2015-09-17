<?php

namespace Paraunit\Command;

use Paraunit\Filter\Filter;
use Paraunit\Runner\CoverageRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CoverageCommand.
 */
class CoverageCommand extends Command
{
    /** @var Filter */
    protected $filter;

    /** @var CoverageRunner */
    protected $coverageRunner;

    /**
     * @param Filter $filter
     * @param CoverageRunner $coverageRunner
     * @param string $name
     */
    public function __construct(Filter $filter, CoverageRunner $coverageRunner, $name = 'Paraunit')
    {
        parent::__construct($name);

        $this->filter = $filter;
        $this->coverageRunner = $coverageRunner;
    }

    protected function configure()
    {
        $this
            ->setName('coverage')
            ->addOption('output-clover', 'clover', InputOption::VALUE_REQUIRED, 'Output file for Clover XML coverage result')
            ->addOption('output-xml', 'xml', InputOption::VALUE_REQUIRED, 'Output file for PHPUnit XML coverage result')
            ->addOption('output-html', 'html', InputOption::VALUE_REQUIRED, 'Output file for HTML coverage result')
            ->addOption('configuration', null, InputOption::VALUE_REQUIRED, 'The PHPUnit XML config file', 'phpunit.xml.dist')
            ->addOption('testsuite', null, InputOption::VALUE_REQUIRED, 'Choice a specific testsuite from your XML config file')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Print verbose debug output');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $testsuite = null;

        if ($input->getOption('testsuite')) {
            $testsuite = $input->getOption('testsuite');
        }

        $config = $input->getOption('configuration');

        $testArray = $this->filter->filterTestFiles($config, $testsuite);

        return $this->coverageRunner->run($testArray, $input, $output);
    }
}
