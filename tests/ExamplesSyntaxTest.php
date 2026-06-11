<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class ExamplesSyntaxTest extends TestCase
{
    private static $serverProcess;
    private static $baseUrl;
    private static $serverLog;

    public static function setUpBeforeClass(): void
    {
        $projectRoot = dirname(__DIR__);
        self::$baseUrl = "http://127.0.0.1:18080";
        self::$serverLog = sys_get_temp_dir() . "/phore-http-client-examples-server.log";

        $command = escapeshellarg(PHP_BINARY)
            . " -S 127.0.0.1:18080 -t "
            . escapeshellarg($projectRoot . "/www");

        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["file", self::$serverLog, "a"],
            2 => ["file", self::$serverLog, "a"]
        ];

        self::$serverProcess = proc_open($command, $descriptors, $pipes, $projectRoot);

        if ( ! is_resource(self::$serverProcess)) {
            throw new \RuntimeException("Unable to start example test server.");
        }

        fclose($pipes[0]);

        $ready = false;
        for ($i = 0; $i < 50; $i++) {
            $body = @file_get_contents(self::$baseUrl . "/test.php?case=200");
            if ($body === "ABC") {
                $ready = true;
                break;
            }
            usleep(100000);
        }

        if ($ready !== true) {
            self::tearDownAfterClass();
            throw new \RuntimeException(
                "Example test server did not become ready. See log: " . self::$serverLog
            );
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }
    }

    public function testAllExamplesCanRun(): void
    {
        $exampleFiles = glob(dirname(__DIR__) . "/examples/*.php");
        sort($exampleFiles);

        $runnableExamples = [];
        foreach ($exampleFiles as $exampleFile) {
            if (strpos(basename($exampleFile), "_") === 0) {
                continue;
            }
            $runnableExamples[] = $exampleFile;
        }

        $this->assertNotEmpty($runnableExamples, "No example files found.");

        foreach ($runnableExamples as $exampleFile) {
            list($exitCode, $stdout, $stderr) = $this->runExample($exampleFile);
            $this->assertSame(
                0,
                $exitCode,
                "Example failed: {$exampleFile}\nSTDOUT:\n{$stdout}\nSTDERR:\n{$stderr}"
            );
        }
    }

    private function runExample($exampleFile)
    {
        $command = escapeshellarg(PHP_BINARY) . " " . escapeshellarg($exampleFile);
        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $env = $_ENV;
        $env["EXAMPLE_BASE_URL"] = self::$baseUrl;

        $process = proc_open($command, $descriptors, $pipes, dirname(__DIR__), $env);

        if ( ! is_resource($process)) {
            throw new \RuntimeException("Unable to execute example: " . $exampleFile);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [$exitCode, $stdout, $stderr];
    }
}
