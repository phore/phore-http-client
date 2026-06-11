<?php

require __DIR__ . "/_bootstrap.php";

$response = phore_http_request(example_base_url() . "/test.php?case=dump")
    ->withMethod("DELETE")
    ->withQueryParams([
        "page" => 2,
        "filter" => "active"
    ])
    ->withHeader("X-Trace-Id", "example-trace-id")
    ->withBearerAuth("demo-token")
    ->withTimeout(1, 5)
    ->send();

$server = $response->getBodyJson();

echo $server["REQUEST_METHOD"] . " " . $server["REQUEST_URI"] . PHP_EOL;
