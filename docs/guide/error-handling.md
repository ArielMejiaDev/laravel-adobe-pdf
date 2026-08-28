# Error Handling

All failures extend a single base exception, `AdobePdfException`, so you can catch
broadly or narrowly.

## The exception hierarchy

```
AdobePdfException
├── AuthenticationException   // 401/403, or missing credentials
├── RateLimitException        // 429 (carries retryAfter)
├── OperationFailedException  // Adobe reported the job "failed"
├── TimeoutException          // exceeded polling.max_attempts
└── AssetUploadException      // failed to upload a source asset
```

Every exception carries structured context:

```php
try {
    app(\ArielMejiaDev\LaravelAdobePdf\Client\AdobePdfClient::class)
        ->submit('extractpdf', $payload);
} catch (\ArielMejiaDev\LaravelAdobePdf\Exceptions\AdobePdfException $e) {
    $e->getMessage();  // "[INVALID_ASSET_ID] The provided asset ID is invalid."
    $e->errorCode;     // "INVALID_ASSET_ID"
    $e->status;        // 400
    $e->context;       // the raw decoded Adobe error body
}
```

`RateLimitException` additionally exposes `->retryAfter` (seconds, from the
`Retry-After` header).

## Where exceptions surface

How a failure is handled depends on **how you dispatched** the operation.

### Synchronous — inspect the process

`dispatchSync()` **never throws for an Adobe-side failure**. It records the failure on
the process and returns it:

```php
$process = LaravelAdobePdf::compress('broken.pdf')->dispatchSync();

if ($process->hasFailed()) {
    logger()->warning('Compression failed', $process->error);
}
```

This keeps the "return a process" contract consistent — you always get a process back
to inspect.

### Asynchronous — queue semantics

With `dispatch()`, the running job handles failures like this:

- **Terminal failures** (Adobe `failed`, or timeout): the process is marked `failed`
  and the job **stops** — it is *not* retried, because retrying a definitively-failed
  operation is pointless.
- **Rate limits** (`429`): the job is **released** back onto the queue and retried
  later, respecting `Retry-After` when present.
- **Other API / network errors**: the job **throws**, so Laravel retries it (bounded
  by `retryUntil()`). When retries are exhausted, the job's `failed()` handler records
  the failure on the process.

So for async work you generally **watch the process record** (or listen for Laravel's
`JobFailed` event), rather than wrapping `dispatch()` in a try/catch.

## Handling rate limits proactively

If you drive the low-level client yourself, you can honor `retryAfter`:

```php
use ArielMejiaDev\LaravelAdobePdf\Exceptions\RateLimitException;

try {
    // ...
} catch (RateLimitException $e) {
    $wait = $e->retryAfter ?? 30;
    // reschedule after $wait seconds
}
```

For normal usage the queue already does this for you.
