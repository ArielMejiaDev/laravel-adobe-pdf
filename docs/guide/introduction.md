# What is Laravel Adobe PDF?

`arielmejiadev/laravel-adobe-pdf` is a Laravel package that wraps the
[Adobe PDF Services API](https://developer.adobe.com/document-services/docs/apis/)
in a fluent, expressive API and processes every operation through Laravel's queue.

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

// Async by default — returns a tracked process, work happens on the queue
$process = LaravelAdobePdf::extract('invoice.pdf')->tables()->text()->dispatch();

// Or run it inline and inspect the result
$process = LaravelAdobePdf::compress('report.pdf')->level('HIGH')->dispatchSync();

if ($process->isSuccessful()) {
    $bytes = $process->output();
}
```

## Why async first?

The Adobe PDF Services API is **itself asynchronous**. A single operation is really
a small workflow:

1. Fetch an OAuth access token.
2. Register an asset and upload the source file.
3. Submit the operation — Adobe responds with `202 Accepted` and a status URL.
4. **Poll** that status URL until it reports `done` (or `failed`).
5. Download the result from a presigned URL.

Modeling that as a blocking, synchronous call would hold an HTTP request or a queue
worker hostage in a `sleep()` loop for as long as Adobe takes. Instead, this package
models the operation as a queued job that **releases itself back onto the queue
between polls** — the worker stays free, and rate limits and transient failures fall
straight onto Laravel's retry machinery.

You still get a synchronous escape hatch with [`dispatchSync()`](/guide/how-it-works#synchronous-execution)
for small jobs and tests.

## Supported operations

| Operation | Method | Adobe endpoint |
| --------- | ------ | -------------- |
| [Create PDF](/operations/create-pdf) | `create()` | `createpdf` |
| [Extract PDF](/operations/extract-pdf) | `extract()` | `extractpdf` |
| [Compress PDF](/operations/compress-pdf) | `compress()` | `compresspdf` |
| [Combine PDF](/operations/combine-pdf) | `combine()` | `combinepdf` |
| [Document Generation](/operations/document-generation) | `generate()` | `documentgeneration` |
| [HTML to PDF](/operations/html-to-pdf) | `html()` | `htmltopdf` |
| [Watermark](/operations/watermark) | `watermark()` | `addwatermark` |

## Requirements

- PHP 8.4+
- Laravel 11, 12 or 13
- An Adobe Developer Console project with **OAuth Server-to-Server** credentials

Ready? Head to [Getting Started](/guide/getting-started).
