<?php

require __DIR__ . "/_bootstrap.php";

$url = phore_url("https://api.example.test/users/{userId}", [
    "userId" => "ada@example.com"
]);

echo (string)$url . PHP_EOL;
