<?php

namespace Paraunit\Tests\Functional\Runner;

use Paraunit\Configuration\PHPUnitConfigFile;
use Paraunit\Runner\Runner;
use Paraunit\Tests\BaseFunctionalTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class RunnerTest.
 */
class RunnerTest extends BaseFunctionalTestCase
{
    public function testAllGreen()
    {
        $outputInterface = new BufferedOutput();

        /** @var Runner $runner */
        $runner = $this->container->get('paraunit.runner.runner');

        $fileArray = array(
            'src/Paraunit/Tests/Stub/ThreeGreenTestStub.php',
        );

        $this->assertEquals(0, $runner->run($fileArray, $outputInterface, new PHPUnitConfigFile('')));

        $output = $outputInterface->fetch();
        $this->assertContains('...', $output);
    }

    public function testMaxRetryEntityManagerIsClosed()
    {
        $outputInterface = new BufferedOutput();

        /** @var Runner $runner */
        $runner = $this->container->get('paraunit.runner.runner');

        $fileArray = array(
            'src/Paraunit/Tests/Stub/EntityManagerClosedTestStub.php',
        );

        $this->assertNotEquals(0, $runner->run($fileArray, $outputInterface, new PHPUnitConfigFile('')));

        $output = $outputInterface->fetch();
        $retryCount = $this->container->getParameter('paraunit.max_retry_count');
        $this->assertContains(str_repeat('A', $retryCount) . 'E', $output);
    }

    /**
     * @group this
     *
     * @dataProvider stubFilePathProvider
     */
    public function testMaxRetryDeadlock($stubFilePath)
    {
        $outputInterface = new BufferedOutput();

        $runner = $this->container->get('paraunit.runner.runner');

        $fileArray = array(
            $stubFilePath,
        );

        $this->assertNotEquals(0, $runner->run($fileArray, $outputInterface, new PHPUnitConfigFile('')));

        $output = $outputInterface->fetch();
        $this->assertContains(str_repeat('A', 3) . 'E', $output);
    }

    public function stubFilePathProvider()
    {
        return array(
            array('src/Paraunit/Tests/Stub/MySQLDeadLockTestStub.php'),
            array('src/Paraunit/Tests/Stub/SQLiteDeadLockTestStub.php'),
        );
    }

    public function testSegFault()
    {
        if (!extension_loaded('sigsegv')) {
            $this->markTestSkipped('The segfault cannot be reproduced in this environment');
        }

        $outputInterface = new BufferedOutput();

        $runner = $this->container->get('paraunit.runner.runner');

        $fileArray = array(
            'src/Paraunit/Tests/Stub/SegFaultTestStub.php',
        );

        $this->assertNotEquals(
            0,
            $runner->run($fileArray, $outputInterface, new PHPUnitConfigFile('')),
            'Exit code should not be 0'
        );

        $output = $outputInterface->fetch();
        $this->assertContains('<halted>X</halted>', $output, 'Missing X output');
        $this->assertContains(
            '1 files with ABNORMAL TERMINATIONS',
            $output,
            'Missing recap title'
        );
        $this->assertContains(
            'SegFaultTestStub.php',
            $output,
            'Missing failing filename'
        );
    }

    public function testWarning()
    {
        $phpunitVersion = new \PHPUnit_Runner_Version();

        if ( ! preg_match('/^5\./', $phpunitVersion->id())) {
            $this->markTestSkipped('PHPUnit < 5 in this env, warnings are not present.');
        }

        $outputInterface = new BufferedOutput();

        $runner = $this->container->get('paraunit.runner.runner');

        $fileArray = array(
            'src/Paraunit/Tests/Stub/MissingProviderTestStub.php',
        );

        $this->assertEquals(
            0,
            $runner->run($fileArray, $outputInterface, new PHPUnitConfigFile('')),
            'Exit code should be 0'
        );

        $output = $outputInterface->fetch();
        $this->assertContains('<warning>W</warning>', $output, 'Missing W output');
        $this->assertContains(
            '1 files with WARNINGS:',
            $output,
            'Missing recap title'
        );
        $this->assertContains(
            '<warning>MissingProviderTestStub.php</warning>',
            $output,
            'Missing warned filename'
        );
    }

    public function testRegressionFatalErrorsRecognizedAsUnknownResults()
    {
        $outputInterface = new BufferedOutput();

        $runner = $this->container->get('paraunit.runner.runner');

        $fileArray = array(
            'src/Paraunit/Tests/Stub/FatalErrorTestStub.php',
        );

        $this->assertNotEquals(0, $runner->run($fileArray, $outputInterface, new PHPUnitConfigFile('phpunit.xml.dist')), 'Exit code should not be 0');

        $output = $outputInterface->fetch();
        $this->assertContains('<halted>X</halted>', $output, 'Missing X output');
        $this->assertContains('1 files with ABNORMAL TERMINATIONS', $output, 'Missing fatal error recap title');
        $this->assertNotContains('1 files with UNKNOWN STATUS:', $output, 'REGRESSION: fatal error mistaken for unknown result');

    }
}