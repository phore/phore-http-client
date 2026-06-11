<?php

require __DIR__ . "/_bootstrap.php";

$response = phore_http_request(example_base_url() . "/test.php?case=500")
    ->send(false);

echo $response->getHttpStatus() . PHP_EOL;
