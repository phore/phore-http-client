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

phore_out("Start");

$err = 0;
$ok = 0;
for ($i=0; $i<200; $i++) {
    $queue->queue(phore_http_request("https://ulan./")->withTimeout(2,10))->then(
        function(PhoreHttpResponse $response) use (&$data, &$ok)  {
            phore_out("OK$ok:");
            $ok++;

        },
        function (PhoreHttpRequestException $ex) use (&$err){
            phore_out("ERR$err:" . $ex->getMessage());
            $err++;
        });
}

$queue->wait();

phore_out("stop");

echo "\nOK: $ok Err: $err\n";
echo $data;
