<?php

namespace Paraunit\Tests\Stub;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class QueueConsoleOutput
 */
class ConsoleOutputStub extends ConsoleOutput implements OutputInterface
{

    protected $outputBuffer;

    function __construct()
    {
        $this->outputBuffer = '';
    }

    /**
     * @param array|string $messages
     * @param int $type
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->outputBuffer .= $messages;
    }

    /**
     * @param array|string $messages
     * @param bool $newline
     * @param int $type
     */
    public function write($messages, $newline = false, $type = 0)
    {
        $this->outputBuffer .= $messages;
    }

    public function getOutput()
    {
        return $this->outputBuffer;
    }

}