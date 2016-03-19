<?php

namespace Tests;

use Paraunit\Configuration\TempFileNameFactory;
use Paraunit\Configuration\Paraunit;
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

        $this->container = Paraunit::buildContainer();
        $this->cleanUpTempDirForThisExecution();
    }

    protected function tearDown()
    {
        $this->cleanUpTempDirForThisExecution();

        parent::tearDown();
    }

    /**
     * @return StubbedParaunitProcess
     */
    public function createLogForProcessFromStubbedLog(StubbedParaunitProcess $process, $stubLog)
    {
        $stubLogFilename = __DIR__ . '/Stub/PHPUnitJSONLogOutput/' . $stubLog . '.json';
        $this->assertTrue(file_exists($stubLogFilename), 'Stub log file missing! ' . $stubLogFilename);

        /** @var TempFileNameFactory $filenameService */
        $filenameService = $this->container->get('paraunit.configuration.temp_filename_factory');
        $filename = $filenameService->getFilenameForLog($process->getUniqueId());

        copy($stubLogFilename, $filename);
    }

    private function cleanUpTempDirForThisExecution()
    {
        if ($this->container) {
            /** @var TempDirectory $tempDirectory */
            $tempDirectory = $this->container->get('paraunit.file.temp_directory');
            Cleaner::cleanUpDir($tempDirectory->getTempDirForThisExecution());
        }
    }
}
