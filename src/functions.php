<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 01.08.18
 * Time: 18:30
 */


function phore_http_request (string $methode, string $url, array $params = []) : \Phore\HttpClient\PhoreHttpRequest
{
    return new \Phore\HttpClient\PhoreHttpRequest($methode, $url, $params);
}
