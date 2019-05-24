<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 20.12.18
 * Time: 16:42
 */

namespace Test;


use Phore\Cache\Cache;
use Phore\ObjectStore\Driver\FileSystemObjectStoreDriver;
use Phore\ObjectStore\ObjectStore;
use PHPUnit\Framework\TestCase;

class CachedRequestTest extends TestCase
{


    public function testBasicCaching()
    {
        system("rm -R /tmp/cache1");
        mkdir ("/tmp/cache1");
        $cache = new Cache(new ObjectStore(new FileSystemObjectStoreDriver("/tmp/cache1")));

        $ret = phore_http_request("http://localhost/test.php?case=200")->withCache($cache)->send();
        $this->assertEquals(false, $ret->isFromCache());

        $ret = phore_http_request("http://localhost/test.php?case=200")->withCache($cache)->send();
        $this->assertEquals(true, $ret->isFromCache());
        $this->assertEquals("ABC", $ret->getBody());
        $this->assertEquals("text/plain", $ret->getContentType());
        $this->assertEquals("UTF-8", $ret->getCharset());
    }



}
