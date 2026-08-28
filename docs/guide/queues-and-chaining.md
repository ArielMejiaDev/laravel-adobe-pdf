# Queues & Chaining

## Choosing a connection and queue

By default operations use the connection and queue from
[`config/adobe-pdf.php`](/guide/configuration). Override them per-operation:

```php
LaravelAdobePdf::compress('scan.pdf')
    ->onConnection('redis')
    ->onQueue('pdfs')
    ->dispatch();
```

Run a worker for that queue:

```bash
php artisan queue:work redis --queue=pdfs
```

## Chaining operations into a pipeline

Every operation can be turned into its underlying job with `toJob()`, so you can
compose several into a single sequential process with Laravel's
[job chaining](https://laravel.com/docs/queues#job-chaining):

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    LaravelAdobePdf::compress('report.pdf')->toJob(),
    LaravelAdobePdf::watermark('report.pdf', 'draft.pdf')->toJob(),
])->dispatch();
```

Each step runs only after the previous one completes. Every step still gets its own
tracked `adobe_pdf_processes` row, so you can follow the whole pipeline's progress.

::: tip
`toJob()` writes the process record immediately (so inputs are staged up front) and
returns the job instance — ready to drop into `Bus::chain()`, `Bus::batch()`, or
dispatch yourself.
:::

## Batching

Because operations are plain jobs, they also work with
[batches](https://laravel.com/docs/queues#job-batching) when you want many independent
operations to run in parallel and report when they're all done:

```php
use Illuminate\Support\Facades\Bus;

Bus::batch([
    LaravelAdobePdf::compress('a.pdf')->toJob(),
    LaravelAdobePdf::compress('b.pdf')->toJob(),
    LaravelAdobePdf::compress('c.pdf')->toJob(),
])->then(function () {
    // all compressions finished
})->dispatch();
```

## Retries & timeouts

Polling and retries are governed by [`polling.backoff` and `polling.max_attempts`](/guide/configuration#key-options).
The job also defines `retryUntil()` as a safety net so a stuck operation can never
loop forever. You generally don't need to set `--tries` on your worker for these jobs.
