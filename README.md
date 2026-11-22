# Phore http-client

[![Actions Status](https://github.com/phore/phore-http-client/workflows/tests/badge.svg)](https://github.com/phore/phore-http-client/actions)

Easy to use http-client with fluent api.

## Example

```php
$response = phore_http_request("http://localhost/test.php?case=200")
    ->send();

echo $response->getBody();
```

More executable examples are available in [`examples/`](examples/).

## Timeouts

Use `withTimeout($connect, $timeout)` to set connection and total timeouts in
seconds, including fractional values. Both parameters accept `null` and default
to `null`; calling `withTimeout()` clears timeout overrides on the cloned request.
The parameters use explicit nullable types for PHP 8.4 and later. If an installed
copy reports an implicit-nullability deprecation, update `phore/http-client`.

## Request caching

Request Caching requires `phore/cache` package to be installed

```
$cache = new Cache(new ObjectStore(new FileSystemObjectStoreDriver("/tmp/cache1")));

$req = phore_http_request("http://localhost/")->withCache($cache)->send();
if ($req->isFromCache() === true)
    echo "From Cache: " . $req->getBody();
```

Examples: see [`examples/`](examples/) and [`examples/README.md`](examples/README.md).
