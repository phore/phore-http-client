<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 11:59
 */

namespace Test;

use Phore\HttpClient\Handler\PhoreHttpLineStream;
use Phore\HttpClient\PhoreHttpAsyncQueue;
use Phore\HttpClient\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpResponse;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . "/../vendor/autoload.php";


//Environment::setup();



$queue = new PhoreHttpAsyncQueue();

//$queue->queue(phore_http_request("http://localhost/test.php?case=wait"));
for ($i=0; $i<200; $i++) {
    $queue->queue(phore_http_request("http://google.de"))->then(function(PhoreHttpResponse $response) {
        echo "Done:" . $response->getHttpStatus();
        echo $response->getBody();
    }, function($err) {
        echo "error: " . $err->getMessage();
    });
}

$queue->wait();


