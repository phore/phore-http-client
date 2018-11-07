<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 06.11.18
 * Time: 13:19
 */

namespace Test;


use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpAsyncQueue;
use Phore\HttpClient\PhoreHttpResponse;
use PHPUnit\Framework\TestCase;

class RequestPoolingTest extends TestCase
{

    public function testRequestPooling()
    {
        $queue = new PhoreHttpAsyncQueue();

        //$queue->queue(phore_http_request("http://localhost/test.php?case=wait"));
        $fail = 0;
        $ok = 0;
        for ($i=0; $i<20; $i++) {
            $queue->queue(phore_http_request("http://localhost"))->then(
                function(PhoreHttpResponse $response) use (&$ok) {
                    $ok++;
                }, function($err) use (&$fail) {
                    $fail++;
                }
                );
        }

        $queue->wait();
        $this->assertEquals(20, $ok);
        $this->assertEquals(0, $fail);

    }


    public function testPoolingTriggersErrorHandlerOn500 ()
    {
        $queue = new PhoreHttpAsyncQueue();
        $fail = 0;
        $ok = 0;
        $queue->queue(phore_http_request("http://localhost/test.php?case=500"))->then(
                function(PhoreHttpResponse $response) use (&$ok) {
                    $ok++;
                }, function($err) use (&$fail) {
                    $fail++;
                });

        $queue->wait();

        $this->assertEquals(1, $fail);
    }

    public function testPoolingTriggersErrorHandlerOn400 ()
    {
        $queue = new PhoreHttpAsyncQueue();
        $fail = 0;
        $ok = 0;
        $queue->queue(phore_http_request("http://localhost/test.php?case=400"))->then(
            function(PhoreHttpResponse $response) use (&$ok) {
                $ok++;
            },
            function(PhoreHttpRequestException $err) use (&$fail) {
                $fail++;
            }
            );

        $queue->wait();

        $this->assertEquals(1, $fail);
    }

}
