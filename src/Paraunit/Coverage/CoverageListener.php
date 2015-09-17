<?php

namespace Paraunit\Coverage;

use Paraunit\Lifecycle\CoverageEvent;

class CoverageListener
{
    /**
     * @param CoverageEvent $event
     */
    public function onCoverageStart(CoverageEvent $event)
    {
        // TODO -- prepare temp dir
        // throw exception if dir is not empty
    }

    /**
     * @param CoverageEvent $event
     */
    public function onCoverageEnd(CoverageEvent $event)
    {
        // throw exception if dir does not exist
        // TODO -- merge all files
    }
}
