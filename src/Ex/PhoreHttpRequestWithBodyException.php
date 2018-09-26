<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 26.09.18
 * Time: 12:33
 */

namespace Phore\HttpClient\Ex;


use Phore\HttpClient\PhoreHttpResponse;

class PhoreHttpRequestWithBodyException extends PhoreHttpRequestException
{
    private $response;

    public function __construct(string $message, PhoreHttpResponse $response, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse() : PhoreHttpResponse
    {
        return $this->response;
    }
}
