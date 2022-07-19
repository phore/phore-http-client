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


    private function with($name, $value)
    {
        $parsed = parse_url($this->url);
        $parsed[$name] = $value;

        $newUrl = "";
        if (isset ($parsed["schema"]))
            $newUrl .= $parsed["schema"];
        $newUrl .= "//";

        if (isset ($parsed["user"]))
            $newUrl .= $parsed["user"] . ":" . $parsed["pass"] . "@";

        if (isset($parsed["host"]))
            $newUrl .= $parsed["host"];
        if (isset($parsed["path"]))
            $newUrl .= $parsed["path"];
        if (isset($parsed["query"]))
            $newUrl .=  "?" . $parsed["query"];
        if (isset($parsed["fragment"]))
            $newUrl .=  "#" . $parsed["fragment"];
        return new self($newUrl);
    }



    public function withUrl(string $url, array $params=[])
    {
        return new self($url, $params);
    }

    public function getScheme() : string
    {
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    public function withScheme(string $scheme) : self
    {
        return $this->withUrl("scheme", $scheme);
    }

    public function getPort() : ?string
    {
        return parse_url($this->url, PHP_URL_PORT);
    }

    public function withPort(int $port) : self
    {
        return $this->withUrl("port", $port);
    }

    public function getUser() : string
    {
        return parse_url($this->url, PHP_URL_USER);
    }

    public function withUser(string $user) : self
    {
        return $this->withUrl("user", $user);
    }

    public function getPass() : string
    {
        return parse_url($this->url, PHP_URL_PASS);
    }

    public function withPass(string $pass) : self
    {
        return $this->withUrl("pass", $pass);
    }

    public function getHost() : string
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    public function withHost(string $hostname) : self
    {
        return $this->withUrl("host", $hostname);
    }

    public function getPath() : string
    {
        return parse_url($this->url, PHP_URL_PATH);
    }

    public function withPath(string $path) : self
    {
        return $this->withUrl("path", $path);
    }

    public function getQuery() : string
    {
        return parse_url($this->url, PHP_URL_QUERY);
    }

    public function withQuery(string $query) : self
    {
        return $this->withUrl("query", $query);
    }

    public function getFragment() : string
    {
        return parse_url($this->url, PHP_URL_FRAGMENT);
    }

    public function withFragment(string $fragment) : self
    {
        return $this->withUrl("fragment", $fragment);
    }
}
