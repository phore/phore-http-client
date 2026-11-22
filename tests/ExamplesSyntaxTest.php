<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class ExamplesSyntaxTest extends TestCase
{
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
        $env["EXAMPLE_BASE_URL"] = TEST_BASE_URL;

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
