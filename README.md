# Phore http-client

[![Actions Status](https://github.com/phore/phore-http-client/workflows/tests/badge.svg)](https://github.com/phore/phore-http-client/actions)

Easy to use http-client with fluent api.

## Tests

Run the tests directly with PHP 8.5, Composer 2 and the PHP extensions `curl`,
`dom`, `mbstring`, `xml`, `xmlwriter` and `yaml`:

```bash
composer install
composer test
```

The test bootstrap starts PHP's built-in web server on a free local port and
stops it after the tests. No Kickstart, Docker or separate web server is required.
GitHub Actions uses the same command with PHP 8.5.

Test dependencies use stable PHPUnit 12.5 and phore/filesystem 1.x releases.
Known dependency deprecations are covered by `phpunit-baseline.xml`; new
deprecations fail the test run.

## Example

```php
$response = phore_http_request("http://localhost/test.php?case=200")
    ->send();

echo $response->getBody();
```

More executable examples are available in [`examples/`](examples/).

## Request caching

Request Caching requires `phore/cache` package to be installed

```
$cache = new Cache(new ObjectStore(new FileSystemObjectStoreDriver("/tmp/cache1")));

$req = phore_http_request("http://localhost/")->withCache($cache)->send();
if ($req->isFromCache() === true)
    echo "From Cache: " . $req->getBody();
```

Examples: see [`examples/`](examples/) and [`examples/README.md`](examples/README.md).
