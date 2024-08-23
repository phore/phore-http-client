<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:50
 */

namespace Phore\HttpClient\Driver;

use Phore\Cache\Cache;
use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpRequest;
use Phore\HttpClient\PhoreHttpResponse;

class PhoreHttp_CurlDriver implements PhoreHttpDriver
{


    private $curlOpt = [
        CURLOPT_SAFE_UPLOAD => true,                // Allow only CurlFile for fileUploads
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_ENCODING => "gzip, deflate",
        CURLOPT_CONNECTTIMEOUT_MS => 10 * 1000
    ];



    public $responseHeaders = [];
    public $responseBody = null;
    public $curlInfoLastResponse = [];

    public function __construct(array $options = [])
    {
        if ( ! function_exists("curl_init")) {
            throw new \Exception("PHP extension 'curl' missing. Install php-curl to use this driver.");
        }

        $this->curlOpt = $options + $this->curlOpt;

        if (defined(CURLOPT_TCP_FASTOPEN))
            $this->curlOpt[CURLOPT_TCP_FASTOPEN] = true;
    }



    public function _buildCurlChannel(PhoreHttpRequest $request, &$cacheKey)
    {
        $req = $request->__get_request_data();

        $curlOpt = $this->curlOpt;

        $url =  $request->getUrl();

        $curlOpt[CURLOPT_URL] = $url;

        $cacheKey = $url;
        if ($req["method"] == "POST") {
            $curlOpt[CURLOPT_POST] = true;
            $cacheKey .= "_POST";
        }
        if ($req["method"] == "PUT") {
            $curlOpt[CURLOPT_CUSTOMREQUEST] = "PUT";
            $curlOpt[CURLOPT_PUT] = true;
            $cacheKey .= "_PUT";
        }
        if ($req["timeout"] !== null) {
            $curlOpt[CURLOPT_TIMEOUT_MS] = (int)($req["timeout"] * 1000);
        }
        if ($req["timeout_connect"] !== null) {
            $curlOpt[CURLOPT_CONNECTTIMEOUT_MS] = (int)($req["timeout_connect"] * 1000);
        }
        if ($req["method"] == "DELETE") {
            $curlOpt[CURLOPT_CUSTOMREQUEST] = "DELETE";
            $cacheKey .= "DELETE";
        }
        if ($req["postBody"] !== null) {
            $curlOpt[CURLOPT_POSTFIELDS] = $req["postBody"];
            $cacheKey .= sha1($req["postBody"]);
        }

        if ($req["basicAuthUser"] !== null) {
            $curlOpt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $curlOpt[CURLOPT_USERPWD] = $req["basicAuthUser"] . ":" . $req["basicAuthPass"];
            $cacheKey .= sha1($req["basicAuthUser"] . ":" . $req["basicAuthPass"]);
        }

        $curlOpt[CURLOPT_HEADERFUNCTION] = function ($curl, $headerLine)  {
            $len = strlen($headerLine);
            $headerLine = trim($headerLine);
            $headArr = explode(": ", $headerLine, 2);
            if (count($headArr) !== 2)
                return $len; // Ignore invalid headers[
            $key = trim(strtolower($headArr[0]));
            $value = trim ($headArr[1]);
            if ( ! isset($this->responseHeaders[$key])) {
                $this->responseHeaders[$key] = [];
            }
            $this->responseHeaders[$key][] = $value;
            return $len;
        };
        $curlOpt[CURLOPT_RETURNTRANSFER] = true;
        if ($req["streamReaderCallback"] !== null) {
            $curlOpt[CURLOPT_RETURNTRANSFER] = false;

            $curlOpt[CURLOPT_WRITEFUNCTION] = function ($curl, $data) use (&$req) {
                $req["streamReaderCallback"]->message($data);
                return strlen($data);
            };
        }

        if ($req["streamWriterCallback"] !== null) {
            if ($req["method"] != "PUT")
                throw new \InvalidArgumentException("steamWriter is only used on http method 'PUT'!");
            $curlOpt[CURLOPT_READFUNCTION] = function ($curl, $fp, $maxDataLen) use (&$req) {

                return $req["streamWriterCallback"]($maxDataLen);
            };
        }

        $ch = curl_init();

        foreach ($curlOpt as $key => $val) {
            if ( ! is_int($key))
                throw new \InvalidArgumentException("Invalid CurlOpt Key: '$key'");
            if ( ! curl_setopt($ch, $key, $val))
                throw new \InvalidArgumentException("Invalid CurlOpt Value: '$key' => '$val'");
        }

        //curl_setopt_array($ch, $curlOpt);

        $headers = [];
        foreach ($req["headers"] as $key => $val) {
            if ( ! is_int($key)) {
                $headers[] = "$key: $val";
            } else {
                $headers[] = $val;
            }
        }

        if (count ($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        return $ch;
    }





    /**
     * @param PhoreHttpRequest $request
     * @return PhoreHttpResponse
     * @throws PhoreHttpRequestException
     */
    public function execRequest(PhoreHttpRequest $request): PhoreHttpResponse
    {
        $req = $request->__get_request_data();
        $cache = $req["_cache"];

        $ch = $this->_buildCurlChannel($request, $cacheKey);

        if ($cache instanceof Cache && $cache->has($cacheKey)) {
            [$responseBody, $this->responseHeaders, $http_status] = $cache->get($cacheKey);
            if ($req["streamReaderCallback"] !== null) {
                $req["streamReaderCallback"]->message($responseBody);
                $req["streamReaderCallback"]->message(null);
            }
            curl_close($ch);
            return new PhoreHttpResponse($request, $http_status, $this->responseHeaders, $responseBody, ["from_cache" => true]);
        } else {
            $responseBody = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->curlInfoLastResponse = curl_getinfo($ch);
        }


        if ($req["streamReaderCallback"] !== null) {
            $req["streamReaderCallback"]->message(null);
        }

        if ($responseBody === false) {
            $msg = curl_error($ch);
            if (curl_errno($ch) === 3)
                $msg = "Malformed request url";
            throw new PhoreHttpRequestException("Request to '{$req["url"]}' failed: Curl Err: " . $msg .", Curl ErrNo:" . curl_errno($ch));
        }
        curl_close($ch);
        if ($cache instanceof Cache) {
            $cache->set($cacheKey, [$responseBody, $this->responseHeaders, $http_status]);
        }
        $response = new PhoreHttpResponse($request, $http_status, $this->responseHeaders, $responseBody, ["from_cache" => false]);

        return $response;
    }

}
