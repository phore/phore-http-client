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

$err = 0;
$ok = 0;
for ($i=0; $i<100; $i++) {
    $queue->queue(phore_http_request("http://localhost/test.php?case=multiLineOutputWithFlush"))->then(
        function(PhoreHttpResponse $response) use (&$data, &$ok)  {
            echo "OK$ok:" . $response->getBody();
            $ok++;

        },
        function (PhoreHttpRequestException $ex) use (&$err){
            echo "ERR$err:" . $ex->getMessage();
            $err++;
        });
}

$queue->wait();

echo "\nOK: $ok Err: $err\n";
echo $data;
