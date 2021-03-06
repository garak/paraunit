<?php

namespace Paraunit\Printer;

use Paraunit\Lifecycle\EngineEvent;
use Paraunit\TestResult\TestResultContainer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FilesRecapPrinter
 * @package Paraunit\Printer
 */
class FilesRecapPrinter extends AbstractFinalPrinter
{
    /**
     * @param EngineEvent $engineEvent
     */
    public function onEngineEnd(EngineEvent $engineEvent)
    {
        $output = $engineEvent->getOutputInterface();

        foreach ($this->logParser->getParsersForPrinting() as $parser) {
            if ($parser instanceof TestResultContainer) {
                $this->printFileRecap($parser, $output);
            }
        }
    }

    /**
     * @param TestResultContainer $testResultContainer
     * @param OutputInterface $output
     */
    private function printFileRecap(TestResultContainer $testResultContainer, OutputInterface $output)
    {
        if (! $testResultContainer->getTestResultFormat()->shouldPrintFilesRecap()) {
            return;
        }

        $filenames = $testResultContainer->getFileNames();

        if (count($filenames)) {
            $tag = $testResultContainer->getTestResultFormat()->getTag();
            $title = $testResultContainer->getTestResultFormat()->getTitle();
            $output->writeln('');
            $output->writeln(
                sprintf(
                    '<%s>%d files with %s:</%s>',
                    $tag,
                    count($filenames),
                    strtoupper($title),
                    $tag
                )
            );

            foreach ($filenames as $fileName) {
                $output->writeln(sprintf(' <%s>%s</%s>', $tag, $fileName, $tag));
            }
        }
    }
}
