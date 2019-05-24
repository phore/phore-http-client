<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 06.11.18
 * Time: 13:14
 */

namespace Test;


use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpRequest;
use PHPUnit\Framework\TestCase;

class BasicRequestTest extends TestCase
{


    public function testBasicRequestsReturnsResponse()
    {
        $result = phore_http_request( "http://localhost/test.php?case=200")->send();
        $this->assertEquals("ABC", $result->getBody());
        $this->assertEquals("text/plain", $result->getContentType());
        $this->assertEquals("UTF-8", $result->getCharset());


        $body = phore_http_request("http://localhost/test.php?case=300")->send()->getBody();
        $this->assertEquals("ABC", $body);


        $body = phore_http_request("http://localhost/test.php?case=500")->send(false)->getBody();
        $this->assertEquals("ABC", $body);
    }



    public function testDeleteRequest()
    {
        $result = phore_http_request("http://localhost/test.php?case=dump")->withMethod("DELETE")->send()->getBodyJson();

        $this->assertEquals("DELETE", $result["REQUEST_METHOD"]);

    }


    public function testPostRequest()
    {
        $result = phore_http_request("http://localhost/test?case=dump")->withPostBody(["abc"=>"abc"])->send()->getBodyJson();
        $this->assertEquals(1, 1);
    }


    public function testExceptionIsThrownOnStatus500()
    {
        $this->expectException(PhoreHttpRequestException::class);
        $body = phore_http_request("http://localhost/test.php?case=500")->send(true)->getBody();
    }


}
