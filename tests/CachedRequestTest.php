<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 20.12.18
 * Time: 16:42
 */

namespace Test;


use PHPUnit\Framework\TestCase;

class CachedRequestTest extends TestCase
{


    public function testNoop()
    {
        $this->assertEquals(true, true);
    }

}
