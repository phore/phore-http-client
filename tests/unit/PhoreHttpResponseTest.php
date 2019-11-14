<?php


use Phore\HttpClient\PhoreHttpRequest;
use Phore\HttpClient\PhoreHttpResponse;
use PHPUnit\Framework\TestCase;

class PhoreHttpResponseTest extends TestCase
{
    public function testExceptionOnGetBodyWhenNotSet()
    {
        $request = new PhoreHttpRequest("http://localhost/test");
        $response = new PhoreHttpResponse($request, 1, [], null);

        $this->expectException(\http\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage("No response body available");
        $response->getBody();
    }

}
