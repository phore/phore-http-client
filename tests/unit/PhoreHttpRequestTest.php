<?php

use Phore\HttpClient\PhoreHttpRequest;
use PHPUnit\Framework\TestCase;

class PhoreHttpRequestTest extends TestCase
{
    public function testTimeoutsAcceptNullableAndNumericValues()
    {
        $request = new PhoreHttpRequest("http://localhost/test");

        foreach ([[null, null], [null, 2.5], [0.25, null], [1, 5], [0, 0]] as $timeouts) {
            list($connect, $timeout) = $timeouts;
            $configured = $request->withTimeout($connect, $timeout);
            $data = $configured->__get_request_data();

            $this->assertNotSame($request, $configured);
            $this->assertSame($connect === null ? null : (float)$connect, $data["timeout_connect"]);
            $this->assertSame($timeout === null ? null : (float)$timeout, $data["timeout"]);
        }

        $this->assertNull($request->__get_request_data()["timeout_connect"]);
        $this->assertNull($request->__get_request_data()["timeout"]);
    }

    public function testOmittedTimeoutsResetOnlyTheClonedRequest()
    {
        $request = (new PhoreHttpRequest("http://localhost/test"))->withTimeout(0.5, 2.5);
        $reset = $request->withTimeout();
        $connectOnly = $request->withTimeout(1.5);

        $this->assertNull($reset->__get_request_data()["timeout_connect"]);
        $this->assertNull($reset->__get_request_data()["timeout"]);
        $this->assertSame(1.5, $connectOnly->__get_request_data()["timeout_connect"]);
        $this->assertNull($connectOnly->__get_request_data()["timeout"]);
        $this->assertSame(0.5, $request->__get_request_data()["timeout_connect"]);
        $this->assertSame(2.5, $request->__get_request_data()["timeout"]);
    }
}
