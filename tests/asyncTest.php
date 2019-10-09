<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 09.10.19
 * Time: 14:56
 */

namespace Wurst;

use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpAsyncQueue;
use Phore\HttpClient\PhoreHttpResponse;

require __DIR__ . "/../vendor/autoload.php";

$queue = new PhoreHttpAsyncQueue();

        //$queue->queue(phore_http_request("http://localhost/test.php?case=wait"));


for ($i=0; $i<2; $i++) {
    $queue->queue(phore_http_request("http://localho2st/test.php?case=multiLineOutputWithFlush"))->then(
        function(PhoreHttpResponse $response) use (&$data) {
            echo "OK:" . $response->getBody();

        },
        function (PhoreHttpRequestException $ex) {
            echo "ERR:" . $ex->getMessage();
        });
}

$queue->wait();
echo $data;
