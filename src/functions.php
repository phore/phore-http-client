<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:30
 */


function phore_http_request (string $url, array $params = []) : \Phore\HttpClient\PhoreHttpRequest
{
    return new \Phore\HttpClient\PhoreHttpRequest($url, $params);
}


function phore_url (string $url=null, array $params = [])
{
    return new \Phore\HttpClient\PhoreUrl($url, $params);
}
