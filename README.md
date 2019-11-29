# Phore http-client

[![Actions Status](https://github.com/phore/phore-http-client/workflows/tests/badge.svg)](https://github.com/phore/phore-http-client/actions)

Easy to use http-client with fluent api.

## Example

```
phore_http_request("http://localhost/test.php?case=200")->withMethod()
```



## Request caching

Request Caching requires `phore/cache` package to be installed

```
$cache = new Cache(new ObjectStore(new FileSystemObjectStoreDriver("/tmp/cache1")));

$req = phore_http_request("http://localhost/")->withCache($cache)->send();
if ($req->isFromCache() === true)
    echo "From Cache: " . $req->getBody();
```

Examples:
