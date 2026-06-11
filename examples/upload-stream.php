<?php

require __DIR__ . "/_bootstrap.php";

$chunks = ["Hello", " ", "World", "!"];

$response = phore_http_request(example_base_url() . "/test.php?case=upload")
    ->withMethod("PUT")
    ->withStreamWriter(function ($maxLen) use (&$chunks) {
        if (count($chunks) === 0) {
            return "";
        }
        return array_shift($chunks);
    })
    ->send();

echo $response->getBody() . PHP_EOL;
