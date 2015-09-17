<?php

namespace Paraunit\Lifecycle;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/***
 * Class EngineEvent
 * @package Paraunit\Lifecycle
 */
class EngineEvent extends Event
{
    // This Event will be triggered before the whole paraunit engine is started
    // Used to inject console styling and print shark logo
    const BEFORE_START = 'engine_event.before_start';

    // This Event will be triggered before the whole paraunit engine is shut down
    // Used to collect and merge coverage results
    const BEFORE_SHUTDOWN = 'engine_event.before_shutdown';

    /** @var  OutputInterface */
    protected $outputInterface;

    /**
     * EngineEvent constructor.
     * @param OutputInterface $outputInterface
     */
    public function __construct(OutputInterface $outputInterface)
    {
        $this->outputInterface = $outputInterface;
    }

    /**
     * @return OutputInterface
     */
    public function getOutputInterface()
    {
        return $this->outputInterface;
    }
}
