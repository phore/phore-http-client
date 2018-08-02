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

    public function __construct($method, $url, array $params=[])
    {
        $this->request["method"] = $method;
        $this->request["url"] = $url;
        $this->driver = new PhoreHttp_CurlDriver();

    }


    public function withMethod(string $method) : self
    {
        $new = clone ($this);
        //$new->request = $this->request;
        $new->request["method"] = $method;
        return $new;
    }

    public function withUrl($url, array $params=[]) : self
    {
        $new = clone ($this);
        //$new->request = $this->request;
        $new->request["url"] = $url;
        return $new;
    }

    public function withQueryParams (array $queryParams=[]) : self
    {
        $new = clone ($this);
        //$new->request = $this->request;
        $new->request["queryParams"] = $queryParams;
        return $new;
    }

    public function withPostData ($postData) : self
    {
        $new = clone ($this);
        //$new->request = $this->request;
        $new->request["postBody"] = $postData;
        return $new;
    }

    public function withHeaders(array $headers = []) : self
    {
        $new = clone ($this);
        //$new->request = $this->request;
        $new->request["headers"] = array_merge($new->request["headers"], $headers);
        return $new;
    }

    public function withBasicAuth(string $username=null, string $passwd=null) : self
    {
        $new = clone ($this);
        //$new->request = $this->request;
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
        //$new->request = $this->request;
        $new->request["streamReaderCallback"] = $fn;
        return $new;
    }


    /**
     * @param bool $throwException
     * @return PhoreHttpResponse
     * @throws PhoreHttpRequestException
     */
    public function send(bool $throwException=true) : PhoreHttpResponse
    {
        $result = $this->driver->execRequest($this);
        if ($result->getHttpStatus() > 400 && $throwException)
            throw new PhoreHttpRequestException("HttpResponse: Server returned status-code '{$result->getHttpStatus()}'", $result, $result->getHttpStatus());
        return $result;
    }

}
