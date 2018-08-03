<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 13:50
 */
namespace Test;



use Phore\HttpClient\Handler\PhoreHttpLineStream;

require __DIR__ . "/../vendor/autoload.php";

phore_http_request("http://localhost/test.php?case=stream")->withStreamReader(
    new PhoreHttpLineStream(function ($line, $index) {
        echo "\nLine " . $index . ":" . $line;

    })
)->send(false);



