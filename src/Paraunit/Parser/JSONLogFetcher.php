<?php

namespace Paraunit\Parser;

use Paraunit\Configuration\JSONLogFilename;
use Paraunit\Process\ParaunitProcessInterface;

/**
 * Class JSONLogFetcher
 * @package Paraunit\Parser
 */
class JSONLogFetcher
{
    const LOG_ENDING_STATUS = 'paraunitEnd';

    /** @var  JSONLogFilename */
    private $fileName;

    /**
     * JSONLogFetcher constructor.
     * @param JSONLogFilename $fileName
     */
    public function __construct(JSONLogFilename $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param ParaunitProcessInterface $process
     * @return array
     */
    public function fetch(ParaunitProcessInterface $process)
    {
        $filePath = $this->fileName->generate($process);
        $fileContent = '';

        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
        }

        $logs = json_decode($this->cleanLog($fileContent));
        $logs[] = $this->createLogEnding();

        return $logs;
    }

    /**
     * @param string $jsonString The dirty output
     * @return string            The normalized log, as an array of JSON objects
     */
    private static function cleanLog($jsonString)
    {
        $splitted = preg_replace('/\}\{/', '},{', $jsonString);

        return '[' . $splitted . ']';
    }

    private function createLogEnding()
    {
        $logEnding = new \stdClass();
        $logEnding->status = self::LOG_ENDING_STATUS;

        return $logEnding;
    }
}
