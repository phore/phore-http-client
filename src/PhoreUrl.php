<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 25.10.18
 * Time: 15:23
 */

namespace Phore\HttpClient;


class PhoreUrl
{
    private $url;

    public function __construct(string $url=null, array $params = [])
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
        $this->url = $url;
    }


    public function __toString()
    {
        return $this->url;
    }


    public function withUrl(string $url, array $params=[])
    {
        return new self($url, $params);
    }

    public function getScheme() : string
    {
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    public function getPort() : string
    {
        return parse_url($this->url, PHP_URL_PORT);
    }

    public function getUser() : string
    {
        return parse_url($this->url, PHP_URL_USER);
    }

    public function getPass() : string
    {
        return parse_url($this->url, PHP_URL_PASS);
    }

    public function getHostname() : string
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    public function getPath() : string
    {
        return parse_url($this->url, PHP_URL_PATH);
    }

    public function getQuery() : string
    {
        return parse_url($this->url, PHP_URL_QUERY);
    }

    public function getFragment() : string
    {
        return parse_url($this->url, PHP_URL_FRAGMENT);
    }
}
