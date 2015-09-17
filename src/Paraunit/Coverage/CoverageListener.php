<?php

namespace Paraunit\Coverage;

use Paraunit\Lifecycle\CoverageEvent;

class CoverageListener
{
    /** @var  string The temp directory for Paraunit */
    private $dir;

    public function __construct()
    {
        $this->dir = sys_get_temp_dir() . '/paraunit/';
    }

    /**
     * @param CoverageEvent $event
     * @throws \Exception
     */
    public function onCoverageStart(CoverageEvent $event)
    {
        if ( ! file_exists($this->dir)) {
            if ( ! mkdir($this->dir)) {
                throw new \Exception("Cannot create temp dir for partial coverage results!");
            }
        }

        if ($this->isTempDirNotEmpty()) {
            throw new \Exception("Cannot continue: temp dir for Paraunit is not empty! ".$this->dir);
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

        var_dump($this->dir);
        $iterator = new \FilesystemIterator($this->dir);
        var_dump(iterator_count ( $iterator ));

        // TODO merge all files
        // TODO move or create merged coverage in correct location
        // TODO clean temp dir
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
