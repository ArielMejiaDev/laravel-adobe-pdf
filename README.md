# Adobe PDF Services Open API Integration using Laravel Http Client, Laravel Jobs, Testing, All using the Fluent Laravel API syntax as always in Laravel Apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arielmejiadev/laravel-adobe-pdf.svg?style=flat-square)](https://packagist.org/packages/arielmejiadev/laravel-adobe-pdf)
[![GitHub Tests Action Status](https://github.com/arielmejiadev/laravel-adobe-pdf/actions/workflows/run-tests.yml/badge.svg)](https://github.com/arielmejiadev/laravel-adobe-pdf/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/arielmejiadev/laravel-adobe-pdf/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/arielmejiadev/laravel-adobe-pdf/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/arielmejiadev/laravel-adobe-pdf.svg?style=flat-square)](https://packagist.org/packages/arielmejiadev/laravel-adobe-pdf)

A fluent, Laravel-native wrapper around the [Adobe PDF Services API](https://developer.adobe.com/document-services/docs/apis/). Every operation — combine, compress, create, document generation, extract, HTML-to-PDF and watermark — is expressed with an elegant builder and processed through a queued job by default, with non-blocking polling, custom exceptions and per-operation tracking in the database.

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

// Async by default — returns a tracked process, work happens on the queue
$process = LaravelAdobePdf::extract('invoice.pdf')->tables()->text()->dispatch();

// Run it inline and inspect the result
$process = LaravelAdobePdf::compress('report.pdf')->level('HIGH')->dispatchSync();

if ($process->isSuccessful()) {
    $bytes = $process->output();
}
```

## Documentation

Full documentation lives at **[arielmejiadev.github.io/laravel-adobe-pdf](https://arielmejiadev.github.io/laravel-adobe-pdf/)**.

The site is built with [VitePress](https://vitepress.dev) and lives in [`docs/`](docs). To run it locally:

```bash
cd docs
npm install
npm run docs:dev
```

It deploys to GitHub Pages automatically via the [`deploy-docs`](.github/workflows/deploy-docs.yml) workflow on every push to `main` that touches `docs/`. Enable it once under **Settings → Pages → Build and deployment → Source: GitHub Actions**.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-adobe-pdf.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-adobe-pdf)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require arielmejiadev/laravel-adobe-pdf
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-adobe-pdf-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-adobe-pdf-config"
```

Then set your Adobe **OAuth Server-to-Server** credentials (from the Adobe Developer Console) in your `.env`:

```dotenv
ADOBE_PDF_CLIENT_ID=your-client-id
ADOBE_PDF_CLIENT_SECRET=your-client-secret

# optional
ADOBE_PDF_QUEUE_CONNECTION=redis
ADOBE_PDF_QUEUE=pdfs
ADOBE_PDF_DISK=local
ADOBE_PDF_PATH=adobe-pdf
```

## How it works

The Adobe PDF Services API is itself asynchronous: you upload an asset, submit an
operation, poll a status URL until it is `done`, then download the result. This
package models that faithfully:

- **Async first.** Each operation is a queued `ProcessAdobePdfOperation` job.
  Polling is **non-blocking** — between polls the job releases itself back onto
  the queue instead of holding a worker in a `sleep()` loop.
- **Sync escape hatch.** `dispatchSync()` runs the same job inline (polling in a
  bounded loop) and returns the finished process — handy for small jobs and tests.
- **Tracked.** Every operation is recorded in the `adobe_pdf_processes` table with
  its status, Adobe error payload and the path to its output.
- **Typed errors.** Failures surface as `RateLimitException`, `AuthenticationException`,
  `OperationFailedException`, `TimeoutException` — all extending `AdobePdfException`.

## Usage

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

// Create a PDF from a Word/Excel/PowerPoint document
LaravelAdobePdf::create('contract.docx')->dispatch();

// Extract text + tables (result is a ZIP of structuredData.json + renditions)
LaravelAdobePdf::extract('invoice.pdf')->text()->tables()->tableFormat('csv')->dispatch();

// Compress
LaravelAdobePdf::compress('scan.pdf')->level('HIGH')->dispatch();

// Combine files (optionally by page range)
LaravelAdobePdf::combine()
    ->add('cover.pdf')
    ->add('body.pdf', [[1, 10]])
    ->dispatch();

// Document generation from a Word template + JSON data
LaravelAdobePdf::generate('template.docx', ['customer' => 'Ada Lovelace'])->dispatch();

// HTML to PDF (from a URL or an .html / zipped bundle)
LaravelAdobePdf::html('https://example.com')->pageSize(8.5, 11)->dispatch();

// Watermark one PDF with another
LaravelAdobePdf::watermark('document.pdf', 'watermark.pdf')->opacity(40)->dispatch();
```

### Choosing a queue

```php
LaravelAdobePdf::compress('scan.pdf')
    ->onConnection('redis')
    ->onQueue('pdfs')
    ->dispatch();
```

### Chaining operations into a pipeline

Because every operation exposes a job via `toJob()`, you can compose them with the
bus — e.g. extract → … → watermark — in a single process:

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    LaravelAdobePdf::compress('report.pdf')->toJob(),
    LaravelAdobePdf::watermark('report.pdf', 'draft.pdf')->toJob(),
])->dispatch();
```

### Inspecting a process

```php
$process = LaravelAdobePdf::process($uuid);

$process->status;        // ProcessStatus enum
$process->isSuccessful();
$process->error;         // ['code' => ..., 'message' => ...] when failed
$process->output();      // the resulting file's bytes
$process->outputUrl();   // a URL to the stored result

// Query them (e.g. for a dashboard)
LaravelAdobePdf::processes()->failed()->latest()->get();
```

### Inputs

Any operation input accepts a path, or a `Source` for finer control:

```php
use ArielMejiaDev\LaravelAdobePdf\Support\Source;

LaravelAdobePdf::create(Source::disk('s3', 'contracts/a.docx'))->dispatch();
LaravelAdobePdf::create(Source::contents($bytes, 'a.docx'))->dispatch();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ArielMejiaDev](https://github.com/ArielMejiaDev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
