<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:41
 */

namespace Phore\HttpClient;


use \InvalidArgumentException;
use Phore\HttpClient\Ex\PhoreHttpRequestException;

class PhoreHttpResponse
{

    private $request;
    private $httpStatus;
    private $responseHeaders;
    private $responseBody;
    private $opts;

    public function __construct(PhoreHttpRequest $request, int $httpStatus, array $responseHeaders, string $responseBody = null, array $opts=[])
    {
        $this->request = $request;
        $this->httpStatus = $httpStatus;
        $this->responseHeaders = $responseHeaders;
        $this->responseBody = $responseBody;
        $this->opts = $opts;
    }


    public function getBody() : string
    {
        if($this->responseBody === null) {
            throw new InvalidArgumentException("No response body available: Possible reason: are you using stream reader?");
        }
        return $this->responseBody;
    }

    public function isFromCache() : bool
    {
        return $this->opts["from_cache"];
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return array|T
     * @throws PhoreHttpRequestException
     */
    public function getBodyJson (string $class = null)
    {
        try {
            $data = phore_json_decode($this->getBody());
            if ($class !== null) {
                if (!function_exists("phore_hydrate"))
                    throw new InvalidArgumentException("Object casting impossible: Package 'phore/hydrator' not installed.");
                $data = phore_hydrate($data, $class);
            }
            return $data;
        } catch(\InvalidArgumentException $e) {
            throw new PhoreHttpRequestException($e->getMessage() . "\nBody:\n" . substr($this->getBody(), 0, 8000) . "\n...", $this, 0, $e);
        }
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

    public function getHeaders (): array
    {
        return $this->responseHeaders;
    }

    public function getCookies () : array
    {
        $cookies = $this->responseHeaders['set-cookie'];
        $ret = [];
        foreach ($cookies as $cookie) {
            preg_match("/([^;\s]+)=([^;\s]+)/", $cookie, $matches);
            $ret[$matches[1]][] = $matches[2];
        }
        return $ret;
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
