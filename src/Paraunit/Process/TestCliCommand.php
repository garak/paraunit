<?php

namespace Paraunit\Process;

use Paraunit\Configuration\PHPUnitBinFile;
use Paraunit\Configuration\PHPUnitConfigFile;
use Paraunit\Configuration\TempFileNameFactory;

/**
 * Class TestCliCommand
 * @package Paraunit\Process
 */
class TestCliCommand implements CliCommandInterface
{
    /** @var  PHPUnitBinFile */
    private $phpUnitBin;

    /** @var  TempFileNameFactory */
    protected $filenameFactory;

    /**
     * TestCliCommand constructor.
     * @param PHPUnitBinFile $phpUnitBin
     * @param TempFileNameFactory $filenameFactory
     */
    public function __construct(PHPUnitBinFile $phpUnitBin, TempFileNameFactory $filenameFactory)
    {
        $this->phpUnitBin = $phpUnitBin;
        $this->filenameFactory = $filenameFactory;
    }

    /**
     * @return string
     */
    public function getExecutable()
    {
       return $this->phpUnitBin->getPhpUnitBin();
    }

    /**
     * @param PHPUnitConfigFile $configFile
     * @param string $uniqueId
     * @return string
     */
    public function getOptions(PHPUnitConfigFile $configFile, $uniqueId)
    {
        return '-c ' . $configFile->getFileFullPath()
            . ' --log-json ' . $this->filenameFactory->getFilenameForLog($uniqueId);
    }
}
