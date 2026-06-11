<?php

require __DIR__ . "/_bootstrap.php";

use Phore\HttpClient\Handler\PhoreHttpLineStream;

$lines = [];

phore_http_request(example_base_url() . "/test.php?case=stream")
    ->withStreamReader(new PhoreHttpLineStream(function ($line, $index) use (&$lines) {
        if ($index < 3) {
            $lines[] = $index . ":" . $line;
        }
    }))
    ->send(false);

echo implode(" | ", $lines) . PHP_EOL;
