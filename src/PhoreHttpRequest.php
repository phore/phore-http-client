<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:31
 */

namespace Phore\HttpClient;


use Phore\HttpClient\Driver\PhoreHttp_CurlDriver;
use Phore\HttpClient\Driver\PhoreHttpDriver;
use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\Ex\PhoreHttpRequestWithBodyException;
use Phore\HttpClient\Handler\PhoreStreamHandler;

class PhoreHttpRequest
{

    /**
     * @var PhoreHttpDriver
     */
    private $driver;


    protected $request = [
        "method"    => "GET",
        "url"       => null,
        "queryParams"   => null,
        "postBody"      => null,
        "streamReaderCallback"=> null,
        "basicAuthUser" => null,
        "basicAuthPass" => null,
        "headers" => []
    ];

    public function __construct($url, array $params=[])
    {
        $this->request["url"] = $this->_parseUrl($url, $params);
        $this->driver = new PhoreHttp_CurlDriver();
    }

    public function getDriver () : PhoreHttp_CurlDriver
    {
        return $this->driver;
    }


    private function _parseUrl(string $url, array $params)
    {
        $url = preg_replace_callback(
            "/\{([a-z0-9\_\-\.]+)\}/i",
            function ($matches) use ($params, $url) {
                if ( ! isset ($params[$matches[1]]))
                    throw new \InvalidArgumentException("Parameter: {{$matches[1]}} not found in url '$url'");
                return urlencode($params[$matches[1]]);
            },
            $url
        );
        return $url;
    }


    public function withMethod(string $method) : self
    {
        $new = clone ($this);
        $new->request["method"] = $method;
        return $new;
    }

    public function withUrl($url, array $params=[]) : self
    {
        $new = clone ($this);
        $url = $this->_parseUrl($url, $params);
        $new->request["url"] = $url;

        return $new;
    }

    public function withQueryParams (array $queryParams=[]) : self
    {
        $new = clone ($this);
        $new->request["queryParams"] = $queryParams;
        return $new;
    }

    public function withPostData ($postData) : self
    {
        $new = clone ($this);
        if ($new->request["method"] === "GET")
            $new->request["method"] = "POST";
        $new->request["postBody"] = $postData;
        return $new;
    }

    public function withHeaders(array $headers = []) : self
    {
        $new = clone ($this);
        $new->request["headers"] = array_merge($new->request["headers"], $headers);
        return $new;
    }

    public function withBasicAuth(string $username=null, string $passwd=null) : self
    {
        $new = clone ($this);
        $new->request["basicAuthUser"] = $username;
        $new->request["basicAuthPass"] = $passwd;
        return $new;
    }

    public function __get_request_data() : array
    {
        return $this->request;
    }


    public function withStreamReader(PhoreStreamHandler $fn) : self
    {
        $new = clone ($this);
        $new->request["streamReaderCallback"] = $fn;
        return $new;
    }


    public function withOAuth2Bearer ($oauth2Token) : self
    {
        return $this->withHeaders(["Authorization" => "Bearer $oauth2Token"]);
    }


    /**
     * @param bool $throwException
     * @return PhoreHttpResponse
     * @throws PhoreHttpRequestException
     */
    public function send(bool $throwException=true) : PhoreHttpResponse
    {
        $result = $this->driver->execRequest($this);
        if ($result->getHttpStatus() >= 400 && $throwException)
            throw new PhoreHttpRequestException("HttpResponse: Server returned status-code '{$result->getHttpStatus()}' on '{$this->request["url"]}'\nBody:\n" . substr($result->getBody(), 0, 8000) . "\n...", $result, $result->getHttpStatus());
        return $result;
    }




}
