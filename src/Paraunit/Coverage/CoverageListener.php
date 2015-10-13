<?php

namespace Paraunit\Coverage;

use Paraunit\Lifecycle\CoverageEvent;

class CoverageListener
{
    /** @var  string The temp directory for Paraunit */
    private $dir;

    public function __construct()
    {
        $this->dir = sys_get_temp_dir() . '/paraunit-coverage/';
    }

    /**
     * @param CoverageEvent $event
     * @throws \Exception
     */
    public function onCoverageStart(CoverageEvent $event)
    {
        if ( ! file_exists($this->dir)) {
            if ( ! mkdir($this->dir, 0777, true)) {
                throw new \Exception("Cannot create temp dir for partial coverage results!");
            }
        }

        if ($this->isTempDirNotEmpty()) {
            throw new \Exception("Cannot continue: temp dir for Paraunit is not empty! " . $this->dir);
        }
    }

    /**
     * @param CoverageEvent $event
     * @throws \Exception
     */
    public function onCoverageEnd(CoverageEvent $event)
    {
        if ( ! file_exists($this->dir)) {
            throw new \Exception("Paraunit temp dir does not exist!");
        }

        $totalCoverage = $this->getCoverageFromTempDir();
        $this->saveReports($totalCoverage, $event);

        $this->clearTempDir();
    }

    /**
     * @return \PHP_CodeCoverage
     */
    private function getCoverageFromTempDir()
    {
        // without this, it's impossible to open the temp files generated by single processes
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->dir);

        $iterator = new \FilesystemIterator($this->dir);
        $totalCoverage = new \PHP_CodeCoverage();

        while ($iterator->valid()) {
            $fileCoverage = include($iterator->getFilename());
            if ($fileCoverage instanceof \PHP_CodeCoverage) {
                $totalCoverage->merge($fileCoverage);
            }

            $iterator->next();
        }

        return $totalCoverage;
    }

    /**
     * @return \PHP_CodeCoverage
     */
    private function clearTempDir()
    {
        $iterator = new \FilesystemIterator($this->dir);

        while ($iterator->valid()) {
            unlink($iterator->getPathname());
            $iterator->next();
        }

        rmdir($this->dir);
    }

    private function saveReports(\PHP_CodeCoverage $totalCoverage, CoverageEvent $event)
    {
        if ($event->getCloverReportFilePath()) {
            $reportGenerator = new \PHP_CodeCoverage_Report_Clover();
            $reportContent = $reportGenerator->process($totalCoverage);
            // TODO -- check for write result
            file_put_contents($event->getCloverReportFilePath(), $reportContent);
        }

        if ($event->getXmlReportPath()) {
            $reportGenerator = new \PHP_CodeCoverage_Report_XML();
            $reportGenerator->process($totalCoverage, $event->getXmlReportPath());
        }

        if ($event->getHtmlReportPath()) {
            $reportGenerator = new \PHP_CodeCoverage_Report_HTML();
            $reportGenerator->process($totalCoverage, $event->getHtmlReportPath());
        }
    }

    /**
     * @return bool
     */
    private function isTempDirNotEmpty()
    {
        $iterator = new \FilesystemIterator($this->dir);

        return $iterator->valid();
    }
}