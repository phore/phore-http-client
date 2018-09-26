<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 11:59
 */

namespace Test;

use Phore\HttpClient\Ex\PhoreHttpRequestWithBodyException;
use Phore\HttpClient\Handler\PhoreHttpLineStream;
use Phore\HttpClient\PhoreHttpRequestException;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . "/../vendor/autoload.php";


Environment::setup();


$result = phore_http_request( "http://localhost/test.php?case=200")->send();
Assert::equal("ABC", $result->getBody());
Assert::equal("text/plain", $result->getContentType());
Assert::equal("UTF-8", $result->getCharset());


$body = phore_http_request("http://localhost/test.php?case=300")->send()->getBody();
Assert::equal("ABC", $body);


$body = phore_http_request("http://localhost/test.php?case=500")->send(false)->getBody();
Assert::equal("ABC", $body);


$body = phore_http_request("http://localhost/{file}", ["file"=>"test.php"])->send()->getBody();
//Assert::equal("ABC", $body);


Assert::exception(function () {
    $body = phore_http_request("http://localhost/test.php?case=500")->send(true)->getBody();
    Assert::equal("ABC", $body);
}, PhoreHttpRequestWithBodyException::class);



phore_http_request("http://localhost/test.php?case=stream")->withStreamReader(
    new PhoreHttpLineStream(function ($line, $index) {
        echo $index . ":" . $line;

    })
)->send(false);

