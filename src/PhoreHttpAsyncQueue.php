<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 25.09.18
 * Time: 15:42
 */

namespace Phore\HttpClient;


use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Phore\HttpClient\Ex\PhoreHttpRequestWithBodyException;
use Phore\HttpClient\Promise\PhoreHttpPromise;

class PhoreHttpAsyncQueue
{

    private $requests = [];

    private $multiHandle = null;

    public function __construct()
    {
        $this->multiHandle = curl_multi_init();
    }

    public function queue(PhoreHttpRequest $request) : PhoreHttpPromise
    {
        $promise = new PhoreHttpPromise();
        $this->requests[] = [$request, $ch = $request->getDriver()->_buildCurlChannel($request), $promise];
        curl_multi_add_handle($this->multiHandle, $ch);
        return $promise;
    }


    public function wait()
    {

        do {
            curl_multi_exec($this->multiHandle, $running);
            curl_multi_select($this->multiHandle);
            foreach ($this->requests as $key => $data) {
                if ($data === null)
                    continue;
                if (curl_error($data[1]) !== '') {
                    $error = curl_error($data[1]);
                    curl_multi_remove_handle($this->multiHandle, $data[1]);
                    curl_close($data[1]);

                    $ex = new PhoreHttpRequestException($error);
                    $data[2]->reject($ex);
                    $this->requests[$key] = null;
                    continue;
                }
                $http_status = curl_getinfo($data[1], CURLINFO_HTTP_CODE);

                if ($http_status > 0 && $http_status < 300 || $http_status >= 400) {

                    $strContent = curl_multi_getcontent($data[1]);
                    $response = new PhoreHttpResponse($data[0], curl_getinfo($data[1], CURLINFO_HTTP_CODE), $data[0]->getDriver()->responseHeaders, $strContent);
                    curl_multi_remove_handle($this->multiHandle, $data[1]);
                    curl_close($data[1]);
                    $this->requests[$key] = null;
                    if ($http_status < 300) {
                        $data[2]->resolve($response);
                    } else {
                        $data[2]->reject(new PhoreHttpRequestException("Request returned status code: $http_status:", $response, $http_status));
                    }
                    continue;
                }
            }
        } while ($running > 0);
    }

}
