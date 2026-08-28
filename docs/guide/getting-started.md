# Getting Started

## Installation

Install the package via Composer:

```bash
composer require arielmejiadev/laravel-adobe-pdf
```

Publish and run the migration that creates the `adobe_pdf_processes` tracking table:

```bash
php artisan vendor:publish --tag="laravel-adobe-pdf-migrations"
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="laravel-adobe-pdf-config"
```

## Credentials

This package authenticates with Adobe's **OAuth Server-to-Server** credential.

1. Go to the [Adobe Developer Console](https://developer.adobe.com/console).
2. Create (or open) a project and add the **PDF Services API**.
3. Choose the **OAuth Server-to-Server** credential and copy the **Client ID** and
   **Client Secret**.

Add them to your `.env`:

```dotenv
ADOBE_PDF_CLIENT_ID=your-client-id
ADOBE_PDF_CLIENT_SECRET=your-client-secret
```

The client ID is also sent automatically as the `x-api-key` header on every request.

## Your first operation

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

$process = LaravelAdobePdf::compress('report.pdf')
    ->level('HIGH')
    ->dispatchSync();

if ($process->isSuccessful()) {
    Storage::disk('local')->put('report-compressed.pdf', $process->output());
}
```

::: tip Async in production
`dispatchSync()` is great for trying things out. In production you'll usually call
`dispatch()` so the work runs on a queue worker. Make sure a worker is running:

```bash
php artisan queue:work
```
:::

## Where do results go?

Each operation stores its result on the configured filesystem disk (default `local`)
under `adobe-pdf/{uuid}/output.{ext}`, and records the path on the process. You read
it back with `$process->output()` (bytes) or `$process->outputUrl()` (a URL). See
[Tracking Processes](/guide/processes).

Next: [Configuration](/guide/configuration).
