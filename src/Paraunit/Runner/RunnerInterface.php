<?php

namespace Paraunit\Runner;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface RunnerInterface
{
    /**
     * @param array $files
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function run(array $files, InputInterface $input, OutputInterface $output);
}
