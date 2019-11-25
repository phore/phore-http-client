<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 11:39
 */

namespace Phore\HttpClient\Ex;


use Phore\HttpClient\PhoreHttpResponse;
use Throwable;

class PhoreHttpRequestException extends \Exception
{

    private $response = null;

    public function __construct(string $message, PhoreHttpResponse $response=null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }


    public function hasResponse() : bool
    {
        return $this->response !== null;
    }


    public function getResponse() : ?PhoreHttpResponse
    {
        return $this->response;
    }

}
