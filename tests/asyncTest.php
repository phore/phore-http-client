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
<<<<<<< HEAD
for ($i=0; $i<10; $i++) {
    $queue->queue(phore_http_request("http://localhost/")->withTimeout(2,10))->then(
        function(PhoreHttpResponse $response) use (&$data, &$ok, $queue)  {

            phore_out("queuing new...");
            $req = phore_http_request("http://localhost/");
            $queue->queue($req)->then(function (PhoreHttpResponse $e) {
                echo "OK Sub";
            });
            phore_out("queuing new...");
            $req = phore_http_request("http://localhost/");
            $queue->queue($req);
            phore_out("OK$ok:");
            $ok++;

        },
        function (PhoreHttpRequestException $ex) use (&$err){

            phore_out("ERR$err:" . $ex->getMessage());
            $err++;
        });
}
=======
while (1) {
    $queue = new PhoreHttpAsyncQueue();
    for ($i = 0; $i < 200; $i++) {
>>>>>>> 5992e0242288830657160d762cc3a70ef37a6ac2

        $queue->queue(phore_http_request("http://localhost/")->withTimeout(2, 10))->then(
            function (PhoreHttpResponse $response) use (&$data, &$ok) {
                phore_out("OK$ok:");
                $ok++;

            },
            function (PhoreHttpRequestException $ex) use (&$err) {
                phore_out("ERR$err:" . $ex->getMessage());
                $err++;
            });
    }

    $queue->wait();

    phore_out("stop");
}
echo "\nOK: $ok Err: $err\n";
echo $data;
