<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 19:26
 */

namespace Phore\HttpClient\Driver;


use Phore\HttpClient\PhoreHttpRequest;
use Phore\HttpClient\PhoreHttpResponse;

interface PhoreHttpDriver
{

    public function execRequest (PhoreHttpRequest $request) : PhoreHttpResponse;

}
