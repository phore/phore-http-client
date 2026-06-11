<?php

require __DIR__ . "/../vendor/autoload.php";

function example_base_url()
{
    $baseUrl = getenv("EXAMPLE_BASE_URL");
    if ($baseUrl === false || $baseUrl === "") {
        $baseUrl = "http://127.0.0.1:8080";
    }
    return rtrim($baseUrl, "/");
}
