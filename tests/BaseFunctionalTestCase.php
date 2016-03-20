<?php

namespace Tests;

use Paraunit\Configuration\TempFilenameFactory;
use Paraunit\Configuration\ParallelConfiguration;
use Paraunit\File\Cleaner;
use Paraunit\File\TempDirectory;
use Tests\Stub\StubbedParaunitProcess;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class BaseFunctionalTestCase
 * @package Paraunit\Tests
 */
abstract class BaseFunctionalTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerBuilder */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->loadContainer();

        $this->cleanUpTempDirForThisExecution();
    }

    protected function tearDown()
    {
        $this->cleanUpTempDirForThisExecution();

        parent::tearDown();
    }

    /**
     * @param StubbedParaunitProcess $process
     * @param string $stubLog
     * @return StubbedParaunitProcess
     */
    public function createLogForProcessFromStubbedLog(StubbedParaunitProcess $process, $stubLog)
    {
        $stubLogFilename = __DIR__ . '/Stub/PHPUnitJSONLogOutput/' . $stubLog . '.json';
        $this->assertTrue(file_exists($stubLogFilename), 'Stub log file missing! ' . $stubLogFilename);

        /** @var TempFilenameFactory $filenameService */
        $filenameService = $this->container->get('paraunit.configuration.temp_filename_factory');
        $filename = $filenameService->getFilenameForLog($process->getUniqueId());

        copy($stubLogFilename, $filename);
    }

    protected function cleanUpTempDirForThisExecution()
    {
        if ($this->container) {
            /** @var TempDirectory $tempDirectory */
            $tempDirectory = $this->container->get('paraunit.file.temp_directory');
            Cleaner::cleanUpDir($tempDirectory->getTempDirForThisExecution());
        }
    }

    protected function loadContainer()
    {
        $configuration = new ParallelConfiguration();

        $this->container = $configuration->buildContainer();
    }
}
