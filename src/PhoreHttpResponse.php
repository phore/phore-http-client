<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:41
 */

namespace Phore\HttpClient;


use Phore\HttpClient\Ex\PhoreHttpRequestException;

class PhoreHttpResponse
{

    private $request;
    private $httpStatus;
    private $responseHeaders;
    private $responseBody;

    public function __construct(PhoreHttpRequest $request, int $httpStatus, array $responseHeaders, string $responseBody)
    {
        $this->request = $request;
        $this->httpStatus = $httpStatus;
        $this->responseHeaders = $responseHeaders;
        $this->responseBody = $responseBody;
    }


    public function getBody() : string
    {
        return $this->responseBody;
    }

    /**
     * @return array
     * @throws PhoreHttpRequestException
     */
    public function getBodyJson () : array
    {
        $json = json_decode($this->getBody(), true);
        if ($json === null)
            throw new PhoreHttpRequestException("Response-Body is not in json format: " . json_last_error_msg() . "\nBody:\n" . substr($this->getBody(),  0, 8000) . "\n...", $this);
        return $json;
    }

    public function getHeader (string $name, $default=null) : string
    {
        if ( ! $this->hasHeader($name)) {
            if ($default === null)
                throw new \InvalidArgumentException("Header '$name' is missing.");
            if ($default instanceof \Exception)
                throw $default;
            return $default;
        }
        return $this->responseHeaders[strtolower($name)][0];
    }


    public function getContentType() : string
    {
        $arr = explode(";", $this->getHeader("Content-Type", new \InvalidArgumentException("No content-type header found in response.")));
        return $arr[0];
    }

    public function getCharset() : string
    {
        $arr = explode(";", $this->getHeader("Content-Type", new \InvalidArgumentException("No content-type header found in response.")));
        $cc = explode ("=", $arr[1], 2);
        if (trim(strtolower($cc[0])) != "charset")
            throw new \InvalidArgumentException("Charset missing in response.");

        return $cc[1];
    }


    public function hasHeader (string $header) : bool
    {
        return isset ($this->responseHeaders[strtolower($header)]);
    }

    public function getHttpStatus () : int
    {
        return $this->httpStatus;
    }

    public function isFailed() : bool 
    {
        return ($this->getHttpStatus() >= 400 || $this->getHttpStatus() < 200);
    }

    public function getRequest() : PhoreHttpRequest
    {
        return $this->request;
    }
    
}
