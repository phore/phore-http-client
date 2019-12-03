<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 06.11.18
 * Time: 13:19
 */

namespace Test;


use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\Handler\PhoreHttpFileStream;
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
        $this->assertEquals(0, $fail);
        $this->assertEquals(20, $ok);

    }


    public function testMultiLineOutputWithFlush ()
    {
        $queue = new PhoreHttpAsyncQueue();

        //$queue->queue(phore_http_request("http://localhost/test.php?case=wait"));


        for ($i=0; $i<2; $i++) {
            $queue->queue(phore_http_request("http://localhost/test.php?case=multiLineOutputWithFlush"))->then(
                function(PhoreHttpResponse $response) use (&$data) {
                    $data = $response->getBody();
                });
        }

        $queue->wait();
        $this->assertEquals("Line 0\nLine 1\n", $data);
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
        $this->assertEquals(0, $ok);
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
        $this->assertEquals(0, $ok);
        $this->assertEquals(1, $fail);
    }

    public function testPoolingWithStreamReader()
    {
        $queue = new PhoreHttpAsyncQueue();

        $tempFile = phore_tempfile();
        $streamHandler = new PhoreHttpFileStream($tempFile);
        $queue->queue(phore_http_request("http://localhost/test.php?case=200")->withStreamReader($streamHandler))
            ->then(
            function(PhoreHttpResponse $response) {
                $this->assertEquals("text/plain;charset=UTF-8", $response->getHeader("Content-Type"));

               // trying to get the response body should fail here
            }, function($err){

            }
        );
        $queue->wait();

        $this->assertEquals("ABC",$tempFile->get_contents());
    }


    public function testStreamReaderResultMustNotHaveBody()
    {
        $queue = new PhoreHttpAsyncQueue();

        $this->expectException(\InvalidArgumentException::class);

        $tempFile = phore_tempfile();
        $streamHandler = new PhoreHttpFileStream($tempFile);
        $queue->queue(phore_http_request("http://localhost/test.php?case=200")->withStreamReader($streamHandler))
            ->then(
            function(PhoreHttpResponse $response) {
                $response->getBody(); // <- should fail
            }
        );
        $queue->wait();
    }

}
