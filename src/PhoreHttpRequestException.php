<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 11:39
 */

namespace Phore\HttpClient;


use Throwable;

class PhoreHttpRequestException extends \Exception
{

    private $response;

    public function __construct(string $message = "", PhoreHttpResponse $response, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse() : PhoreHttpResponse
    {
        return $this->response;
    }

}
