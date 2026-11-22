<?php

use PHPUnit\Framework\TestCase;

class SourceSyntaxTest extends TestCase
{
    public function testSourceFilesCompileWithoutDeprecations()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname(__DIR__, 2) . "/src", FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== "php") {
                continue;
            }

            $path = $file->getPathname();
            $command = escapeshellarg(PHP_BINARY)
                . " -d xdebug.mode=off -d error_reporting=-1 -d display_errors=stderr -l "
                . escapeshellarg($path);
            $output = [];
            exec($command . " 2>&1", $output, $exitCode);

            $this->assertSame(0, $exitCode, implode("\n", $output));
            $this->assertSame(["No syntax errors detected in " . $path], $output);
        }
    }
}
