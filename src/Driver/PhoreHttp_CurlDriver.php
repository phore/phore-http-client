<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:50
 */

namespace Phore\HttpClient\Driver;


use http\Env\Response;
use Phore\HttpClient\PhoreHttpRequest;
use Phore\HttpClient\PhoreHttpRequestException;
use Phore\HttpClient\PhoreHttpResponse;

class PhoreHttp_CurlDriver implements PhoreHttpDriver
{


    private $curlOpt = [
        CURLOPT_SAFE_UPLOAD => true,                // Allow only CurlFile for fileUploads
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 10
    ];


    /**
     * @param PhoreHttpRequest $request
     * @return PhoreHttpResponse
     * @throws PhoreHttpRequestException
     */
    public function execRequest(PhoreHttpRequest $request): PhoreHttpResponse
    {
        $req = $request->__get_request_data();

        $curlOpt = $this->curlOpt;

        $url =  $req["url"];
        if ($req["queryParams"] !== null) {
            if (strpos($url, "?") === false) {
                $url .= "?";
            } else {
                $url .= "&";
            }
            $url .= http_build_str($req["queryParams"]);
        }
        $curlOpt[CURLOPT_URL] = $url;


        if (count($req["headers"]) > 0) {
            $curlOpt[CURLOPT_HTTPHEADER] = $req["headers"];
        }


        if ($req["method"] == "POST") {
            $curlOpt[CURLOPT_POST] = true;
            if ($req["postBody"] !== null)
                $curlOpt[CURLOPT_POSTFIELDS] = $req["postBody"];
        }

        if ($req["basicAuthUser"] !== null) {
            $curlOpt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $curlOpt[CURLOPT_USERPWD] = $req["basicAuthUser"] . ":" . $req["basicAuthPass"];
        }

        $responseHeaders = [];
        $curlOpt[CURLOPT_HEADERFUNCTION] = function ($curl, $headerLine) use (&$responseHeaders) {
            $len = strlen($headerLine);
            $headerLine = trim($headerLine);
            $headArr = explode(": ", $headerLine, 2);
            if (count($headArr) !== 2)
                return $len; // Ignore invalid headers[
            $key = trim(strtolower($headArr[0]));
            $value = trim ($headArr[1]);
            if ( ! isset($responseHeaders[$key])) {
                $responseHeaders[$key] = [];
            }
            $responseHeaders[$key][] = $value;
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
        $ch = curl_init();

        curl_setopt_array($ch, $curlOpt);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $responseBody = curl_exec($ch);$response = new PhoreHttpResponse($request, $http_status, $responseHeaders, $responseBody);
        if ($responseBody === false) {
            throw new PhoreHttpRequestException("Request to '{$url}' failed: " . curl_error($ch), $response, $http_status);
        }
        
        curl_close($ch);
        if ($req["streamReaderCallback"] !== null) {
            $req["streamReaderCallback"]->message(null);
        }

        
        

        return $response;
    }
}
