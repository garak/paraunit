<?php

namespace Paraunit\Lifecycle;


use Symfony\Component\EventDispatcher\Event;

class CoverageEvent extends Event
{
    const COVERAGE_START = 'coverage.start';
    const COVERAGE_END = 'coverage.end';

    /** @var  string */
    protected $cloverReportFilePath;

    /** @var  string */
    protected $xmlReportPath;

    /** @var  string */
    protected $htmlReportPath;

    /**
     * CoverageEvent constructor.
     * @param string $cloverReportFilePath
     * @param string $xmlReportPath
     * @param string $htmlReportPath
     */
    public function __construct($cloverReportFilePath = null, $xmlReportPath = null, $htmlReportPath = null)
    {
        $this->cloverReportFilePath = $cloverReportFilePath;
        $this->xmlReportPath = $xmlReportPath;
        $this->htmlReportPath = $htmlReportPath;
    }

    /**
     * @return string | null
     */
    public function getCloverReportFilePath()
    {
        return $this->cloverReportFilePath;
    }

    /**
     * @return string | null
     */
    public function getXmlReportPath()
    {
        return $this->xmlReportPath;
    }

    /**
     * @return string | null
     */
    public function getHtmlReportPath()
    {
        return $this->htmlReportPath;
    }
}
