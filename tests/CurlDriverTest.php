<?php

namespace Test;

use Phore\HttpClient\Driver\PhoreHttp_CurlDriver;
use Phore\HttpClient\PhoreHttpRequest;
use PHPUnit\Framework\TestCase;

class CurlDriverTest extends TestCase
{
    public function testOverrideOptions()
    {
        $curlDriver = new PhoreHttp_CurlDriver();
        $class = new \ReflectionClass('Phore\HttpClient\Driver\PhoreHttp_CurlDriver');
        $constr = $class->getConstructor();
        $constr->invokeArgs($curlDriver, [[CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIESESSION => true]]);
        $prop = $class->getProperty("curlOpt");
        $prop->setAccessible(true);
        $curlOpt = $prop->getValue($curlDriver);

        $this->assertFalse($curlOpt[CURLOPT_FOLLOWLOCATION]);
        $this->assertTrue($curlOpt[CURLOPT_COOKIESESSION]);
    }
}
