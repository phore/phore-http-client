<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:50
 */

namespace Phore\HttpClient\Driver;

use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpRequest;
use Phore\HttpClient\PhoreHttpResponse;

class PhoreHttp_CurlDriver implements PhoreHttpDriver
{


    private $curlOpt = [
        CURLOPT_SAFE_UPLOAD => true,                // Allow only CurlFile for fileUploads
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TCP_FASTOPEN => true
    ];


    public $responseHeaders = [];
    public $responseBody = null;


    public function __construct()
    {
        if ( ! function_exists("curl_init")) {
            throw new \Exception("PHP extension 'curl' missing. Install php-curl to use this driver.");
        }
    }


    public function _buildCurlChannel(PhoreHttpRequest $request)
    {
        $req = $request->__get_request_data();

        $curlOpt = $this->curlOpt;

        $url =  $request->getUrl();
        
        $curlOpt[CURLOPT_URL] = $url;


        if (count($req["headers"]) > 0) {
            $curlOpt[CURLOPT_HTTPHEADER] = $req["headers"];
        }

        if ($req["method"] == "POST") {
            $curlOpt[CURLOPT_POST] = true;
        }
        if ($req["method"] == "PUT") {
            $curlOpt[CURLOPT_PUT] = true;
        }
        if ($req["method"] == "DELETE") {
            $curlOpt[CURLOPT_CUSTOMREQUEST] = "DELETE";
        }
        if ($req["postBody"] !== null) {
            $curlOpt[CURLOPT_POSTFIELDS] = $req["postBody"];
        }

        if ($req["basicAuthUser"] !== null) {
            $curlOpt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $curlOpt[CURLOPT_USERPWD] = $req["basicAuthUser"] . ":" . $req["basicAuthPass"];
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

        curl_setopt_array($ch, $curlOpt);

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
        $ch = $this->_buildCurlChannel($request);

        $responseBody = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($req["streamReaderCallback"] !== null) {
            $req["streamReaderCallback"]->message(null);
        }

        if ($responseBody === false) {
            throw new PhoreHttpRequestException("Request to '{$req["url"]}' failed: " . curl_error($ch));
        }
        curl_close($ch);
        $response = new PhoreHttpResponse($request, $http_status, $this->responseHeaders, $responseBody);

        return $response;
    }


}
