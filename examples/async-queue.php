<?php

require __DIR__ . "/_bootstrap.php";

use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpAsyncQueue;
use Phore\HttpClient\PhoreHttpResponse;

$successCount = 0;
$queue = new PhoreHttpAsyncQueue();

for ($i = 0; $i < 3; $i++) {
    $queue->queue(phore_http_request(example_base_url() . "/test.php?case=200"))
        ->then(
            function (PhoreHttpResponse $response) use (&$successCount) {
                if ($response->getHttpStatus() === 200) {
                    $successCount++;
                }
            },
            function (PhoreHttpRequestException $exception) {
                throw $exception;
            }
        );
}

$queue->wait();

echo $successCount . PHP_EOL;
