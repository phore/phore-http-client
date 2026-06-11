<?php

require __DIR__ . "/_bootstrap.php";

$response = phore_http_request(example_base_url() . "/test.php?case=dump")
    ->withBodyJson([
        "name" => "Ada Lovelace",
        "role" => "developer"
    ])
    ->withHeader("Accept", "application/json")
    ->send();

$server = $response->getBodyJson();

echo $server["REQUEST_METHOD"] . PHP_EOL;
