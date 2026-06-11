<?php

require __DIR__ . "/_bootstrap.php";

$response = phore_http_request(example_base_url() . "/test.php?case=200")
    ->send();

echo $response->getHttpStatus() . " " . $response->getBody() . PHP_EOL;
