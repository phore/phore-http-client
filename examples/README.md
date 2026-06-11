# Examples

All PHP files in this directory are executable usage examples for `phore/http-client`.

## Run a single example

```bash
php examples/basic-get.php
```

Examples that perform HTTP requests expect a test server URL in `EXAMPLE_BASE_URL`.
During automated tests, this is provided automatically.

```bash
EXAMPLE_BASE_URL=http://127.0.0.1:18080 php examples/post-json.php
```

## Included examples

- `basic-get.php` — send a simple GET request
- `configure-request.php` — configure method, query params, headers, bearer auth and timeouts
- `post-json.php` — send a JSON body
- `ignore-http-errors.php` — inspect non-2xx responses via `send(false)`
- `stream-lines.php` — consume a response incrementally with `PhoreHttpLineStream`
- `async-queue.php` — queue multiple requests in parallel
- `upload-stream.php` — stream a PUT request body
- `url-template.php` — build a URL from a template with `phore_url()`
