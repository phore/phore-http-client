<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 25.09.18
 * Time: 15:42
 */

namespace Phore\HttpClient;


use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\Promise\PhoreHttpPromise;

class PhoreHttpAsyncQueue
{

    
    private $requests = [];

    private $multiHandle = null;

    private $onErrorCb = null;
    private $onSuccessCb = null;
    
    public function __construct()
    {
        $this->multiHandle = curl_multi_init();
    }

    public function queue(PhoreHttpRequest $request) : PhoreHttpPromise
    {
        $promise = new PhoreHttpPromise();
        $this->requests[] = [$request, $ch = $request->getDriver()->_buildCurlChannel($request, $cacheKey), $promise];
        curl_multi_add_handle($this->multiHandle, $ch);
        return $promise;
    }

    
    public function setOnError(callable $errorCb)
    {
        $this->onErrorCb = $errorCb;
    }
    
    public function setOnSuccess(callable $successCb)
    {
        $this->onSuccessCb = $successCb;
    }
    

    public function wait()
    {
        $noRun = 0;

        do {

            curl_multi_exec($this->multiHandle, $running);
            //curl_multi_select($this->multiHandle);
            $infoRead = curl_multi_info_read($this->multiHandle);

            //echo "\nRunning $running " . print_r($infoRead, true);
            foreach ($this->requests as $key => $data) {
                if ($data === null)
                    continue;

                if ($infoRead["handle"] !== $data[1])
                    continue; // Not my channel

                if ($infoRead["result"] !== CURLE_OK) {
                    $msg = [
                        CURLE_UNSUPPORTED_PROTOCOL => "Unsupported Protocol",
                        CURLE_FAILED_INIT => "Failed Init",
                        CURLE_URL_MALFORMAT => "Url Malformat",
                        CURLE_URL_MALFORMAT_USER => "Url Malformat User",
                        CURLE_COULDNT_RESOLVE_PROXY => "Coudnt Resolve Proxy",
                        CURLE_COULDNT_RESOLVE_HOST => "Couldnt Resolve Host",
                        CURLE_COULDNT_CONNECT => "Coldnt Connect",
                        CURLE_HTTP_NOT_FOUND => "Http Not Found",
                    ];
                    curl_multi_remove_handle($this->multiHandle, $data[1]);
                    curl_close($data[1]);

                    $error = $msg[$infoRead["result"]] ?? "Curle_error: {$infoRead["result"]}";

                    $ex = new PhoreHttpRequestException("Connection error: $error (Request: '" . $data[0]->getUrl() . "')");
                    if ($this->onErrorCb !== null)
                        ($this->onErrorCb)($ex);
                    $data[2]->reject($ex);
                    $this->requests[$key] = null;
                    continue;
                }

                $http_status = curl_getinfo($data[1], CURLINFO_RESPONSE_CODE);;
                if ($infoRead["result"] === CURLE_OK && $http_status > 0 && $http_status < 300 || $http_status >= 400) {

                    $strContent = curl_multi_getcontent($data[1]);
                    $response = new PhoreHttpResponse($data[0], curl_getinfo($data[1], CURLINFO_RESPONSE_CODE), $data[0]->getDriver()->responseHeaders, $strContent);
                    curl_multi_remove_handle($this->multiHandle, $data[1]);
                    curl_close($data[1]);
                    $this->requests[$key] = null;
                    if ($http_status < 300) {
                         if ($this->onSuccessCb !== null)
                            ($this->onSuccessCb)($response);
                        $data[2]->resolve($response);
                    } else {
                        if (strlen($strContent) === 0) {
                            $body = "(empty body)";
                        } elseif (strlen($strContent) < 8000) {
                            $body = "'" . $strContent . "'"; // full body output
                        } else {
                            $body = "'". substr($strContent, 0, 8000) . "\n...'";
                        }
                        $ex =  new PhoreHttpRequestException("HttpResponse: Server returned status-code '{$http_status}' on '{$response->getRequest()->getUrl()}'\nBody:\n" . $body, $response, $response->getHttpStatus());
                        if ($this->onErrorCb !== null)
                            ($this->onErrorCb)($ex);
                        $data[2]->reject($ex);
                    }
                    continue;
                }
            }

            $reqLeft = 0;
            foreach ($this->requests as $key => $value) {
                if ($value !== null) {
                    $reqLeft++;
                    break;
                }
            }

            usleep(100);
            // Wait until next run returned null as well (for requeueing)
        } while ($reqLeft > 0);
    }

}
