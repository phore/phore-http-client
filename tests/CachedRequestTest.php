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
        $cacheDir = phore_dir(sys_get_temp_dir() . "/phore-http-client-cache-" . bin2hex(random_bytes(8)));
        $cacheDir->mkdir();

        try {
            $cache = new Cache(new ObjectStore(new FileSystemObjectStoreDriver((string)$cacheDir)));

            $ret = phore_http_request(TEST_BASE_URL . "/test.php?case=200")->withCache($cache)->send();
            $this->assertEquals(false, $ret->isFromCache());

            $ret = phore_http_request(TEST_BASE_URL . "/test.php?case=200")->withCache($cache)->send();
            $this->assertEquals(true, $ret->isFromCache());
            $this->assertEquals("ABC", $ret->getBody());
            $this->assertEquals("text/plain", $ret->getContentType());
            $this->assertEquals("UTF-8", $ret->getCharset());
        } finally {
            $cacheDir->rmDir(true);
        }
    }



}
