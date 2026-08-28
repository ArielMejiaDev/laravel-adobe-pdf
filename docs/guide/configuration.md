# Configuration

After publishing the config file it lives at `config/adobe-pdf.php`. Every value can
be driven from the environment.

## Environment variables

```dotenv
# Credentials (required)
ADOBE_PDF_CLIENT_ID=your-client-id
ADOBE_PDF_CLIENT_SECRET=your-client-secret

# Queue (optional)
ADOBE_PDF_QUEUE_CONNECTION=redis
ADOBE_PDF_QUEUE=pdfs

# Storage (optional)
ADOBE_PDF_DISK=local
ADOBE_PDF_PATH=adobe-pdf

# Advanced (optional)
ADOBE_PDF_BASE_URL=https://pdf-services.adobe.io
ADOBE_PDF_TOKEN_CACHE_STORE=redis
```

## The config file

```php
return [

    'base_url' => env('ADOBE_PDF_BASE_URL', 'https://pdf-services.adobe.io'),

    'client_id' => env('ADOBE_PDF_CLIENT_ID'),
    'client_secret' => env('ADOBE_PDF_CLIENT_SECRET'),

    // Access tokens are cached until shortly before they expire.
    'token' => [
        'store' => env('ADOBE_PDF_TOKEN_CACHE_STORE'),
        'key' => 'adobe-pdf.access-token',
        'leeway' => 60,
    ],

    // Which connection/queue operation jobs are pushed onto.
    'queue' => [
        'connection' => env('ADOBE_PDF_QUEUE_CONNECTION'),
        'queue' => env('ADOBE_PDF_QUEUE'),
    ],

    // Non-blocking polling: release every "backoff" seconds, give up after
    // "max_attempts" polls.
    'polling' => [
        'backoff' => 5,
        'max_attempts' => 60,
    ],

    'http' => [
        'timeout' => 30,
        'retry' => [
            'times' => 3,
            'sleep' => 250,
        ],
    ],

    // Where source files are staged and results are stored.
    'storage' => [
        'disk' => env('ADOBE_PDF_DISK', 'local'),
        'path' => env('ADOBE_PDF_PATH', 'adobe-pdf'),
    ],

    'tracking' => [
        'enabled' => true,
        'table' => 'adobe_pdf_processes',
    ],

];
```

## Key options

### `polling.backoff` / `polling.max_attempts`

Together these bound how long an operation may take. With the defaults a job polls
every **5 seconds** for up to **60 attempts** (~5 minutes) before it times out with a
[`TimeoutException`](/guide/error-handling). Tune them to your documents' size and your
queue's latency.

### `queue.connection` / `queue.queue`

The default connection and queue for operation jobs. You can always override these
per-operation with [`onConnection()` / `onQueue()`](/guide/queues-and-chaining).

### `storage.disk` / `storage.path`

The Laravel filesystem disk used both to **stage** uploaded inputs and to **store**
results. Point it at `s3` (or any disk) to keep generated documents off the local box.

### `token.store`

Access tokens are cached (keyed by `token.key`) until `leeway` seconds before they
expire. Set `token.store` to a specific cache store, or leave it `null` for the
default one.
