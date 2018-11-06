<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 06.11.18
 * Time: 13:17
 */

namespace Test;


use Phore\HttpClient\Handler\PhoreHttpLineStream;
use PHPUnit\Framework\TestCase;

class StreamingRequestTest extends TestCase
{


    public function testChunkReadingWorks()
    {
        phore_http_request("http://localhost/test.php?case=stream")->withStreamReader(
            new PhoreHttpLineStream(function ($line, $index) use (&$data) {
                $data .= $index . ":" . $line;

            })
        )->send(false);
        $this->assertEquals(106785, strlen($data));
    }

}
