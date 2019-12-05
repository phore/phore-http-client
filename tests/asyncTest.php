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



        //$queue->queue(phore_http_request("http://localhost/test.php?case=wait"));

phore_out("Start");

$err = 0;
$ok = 0;
$queue = new PhoreHttpAsyncQueue();
for ($i=0; $i<10; $i++) {

    $queue->queue(phore_http_request("http://localhost/test.php?case=wait")->withTimeout(2,11))->then(
        function(PhoreHttpResponse $response) use (&$data, &$ok, $queue)  {
            /*
            phore_out("queuing new...");
            $req = phore_http_request("http://localhost/");
            $queue->queue($req)->then(function (PhoreHttpResponse $e) {
                echo "OK Sub";
            });
            */
            phore_out("OK$ok:");
            $ok++;

        },
        function (PhoreHttpRequestException $ex) use (&$err){

            phore_out("ERR$err:" . $ex->getMessage());
            $err++;
        });

}
$queue->wait();

echo "\nOK: $ok Err: $err\n";
echo $data;
