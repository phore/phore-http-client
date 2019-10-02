<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:31
 */

namespace Phore\HttpClient;


use Phore\Cache\Cache;
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

    /**
     * @var self
     */
    private static $lastRequest = null;
    private static $lastResponse = null;

    protected $request = [
        "method"    => "GET",
        "url"       => null,
        "queryParams"   => null,
        "postBody"      => null,
        "streamReaderCallback"=> null,
        "streamWriterCallback"=> null,
        "basicAuthUser" => null,
        "basicAuthPass" => null,
        "timeout_connect" => null,
        "timeout" => null,
        "headers" => [],
        "meta" => null,
        "_cache" => null
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

    public function withCache(Cache $cache) : self
    {
        $new = clone ($this);
        $new->request["_cache"] = $cache;
        return $new;
    }

    public function withMethod(string $method) : self
    {
        $new = clone ($this);
        $new->request["method"] = $method;
        return $new;
    }

    public function withMeta($metaData) : self
    {
        $new = clone($this);
        $new->request["meta"] = $metaData;
        return $new;
    }
    
    public function getMeta() 
    {
        return $this->request["meta"];
    }
    
    public function withUrl($url, array $params=[]) : self
    {
        $new = clone ($this);
        $url = $this->_parseUrl($url, $params);
        $new->request["url"] = $url;

        return $new;
    }

    /**
     * Return the full request url (including queryParameters)
     * 
     * @return string
     */
    public function getUrl() : string
    {
        $url = $this->request["url"];
        if ($this->request["queryParams"] !== null) {
            if (strpos($url, "?") === false) {
                $url .= "?";
            } else {
                $url .= "&";
            }
            $url .= \http_build_query($this->request["queryParams"]);
        }
        
        return $url;
    }

    /**
     * Set timeouts in seconds
     *
     * @param float|null $connect
     * @param float|null $timeout
     * @return PhoreHttpRequest
     */
    public function withTimeout(float $connect=null, float $timeout=null) : self
    {
        $new = clone ($this);
        $new->request["timeout_connect"] = $connect;
        $new->request["timeout"] = $timeout;
        return $new;
    }

    public function withQueryParams (array $queryParams=[]) : self
    {
        $new = clone ($this);
        $new->request["queryParams"] = $queryParams;
        return $new;
    }

    /**
     * @param $postData string|array|object
     * @return PhoreHttpRequest
     */
    public function withPostBody($postBody = "") : self 
    {
        $new = clone ($this);
        if ($new->request["method"] === "GET")
            $new->request["method"] = "POST";
        if (is_array($postBody) || is_object($postBody)) {
            $postBody = phore_json_encode($postBody);
        }
        $new->request["headers"]["Content-Type"] = "application/json";
        $new->request["postBody"] = $postBody;
        return $new;
    }

    /**
     * Send data x-www-form-urlencoded
     * 
     * @param array $formData
     * @return PhoreHttpRequest
     */
    public function withPostFormBody(array $formData) : self
    {
        $new = clone ($this);
        if ($new->request["method"] === "GET")
            $new->request["method"] = "POST";

        $new->request["headers"]["Content-Type"] = "application/x-www-form-urlencoded";

        $new->request["postBody"] = http_build_query($formData);
        return $new;
    }

    /**
     * @deprecated use withPostBody() or withPostFormData()
     * @param string $postData
     * @return PhoreHttpRequest
     *
     */
    public function withPostData ($postData="") : self
    {
        return $this->withPostBody($postData);
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

    public function withStreamWriter(callable $fn) : self
    {
        $new = clone($this);
        $new->request["streamWriterCallback"] = $fn;
        return $new;
    }


    public function withBearerAuth ($oauth2Token) : self
    {
        return $this->withHeaders(["Authorization" => "Bearer $oauth2Token"]);
    }

    
    public static function GetLastRequest() : ?self 
    {
        return self::$lastRequest;
    }
    
    public static function GetLastResponse() : ?PhoreHttpResponse
    {
        return self::$lastResponse;
    }

    /**
     * @param bool $throwExceptionOnBodyStatusCode
     * Throws an exception if the body status code is bigger/equals 400
     * Otherwise only connection errors trigger exceptions
     * @return PhoreHttpResponse
     * @throws PhoreHttpRequestException
     */
    public function send(bool $throwExceptionOnBodyStatusCode=true) : PhoreHttpResponse
    {
        self::$lastRequest = $this;
        $result = $this->driver->execRequest($this);
        self::$lastResponse = $result;
        if ($result->getHttpStatus() >= 400 && $throwExceptionOnBodyStatusCode) {
            $body = $result->getBody();
            if (strlen($body) === 0) {
                $body = "(empty body)";
            } elseif (strlen($body) < 8000) {
                $body = "'" . $body . "'"; // full body output
            } else {
                $body = "'". substr($body, 0, 8000) . "\n...'";
            }
            throw new PhoreHttpRequestException("HttpResponse: Server returned status-code '{$result->getHttpStatus()}' on '{$this->request["url"]}'\nBody:\n" . $body, $result, $result->getHttpStatus());
        }
        return $result;
    }




}
