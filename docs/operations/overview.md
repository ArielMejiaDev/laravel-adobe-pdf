# Operations Overview

Each operation is a fluent builder returned by a method on the `LaravelAdobePdf`
facade. You configure it, then finish with a terminal method.

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::extract('invoice.pdf')  // ← builder
    ->tables()->text()                   // ← options
    ->dispatch();                        // ← terminal
```

## The operations

| Method | Builds | Does |
| ------ | ------ | ---- |
| [`create()`](/operations/create-pdf) | `CreatePdf` | Create a PDF from Office/text/image files |
| [`extract()`](/operations/extract-pdf) | `ExtractPdf` | Extract text, tables & figures as structured JSON |
| [`compress()`](/operations/compress-pdf) | `CompressPdf` | Reduce a PDF's file size |
| [`combine()`](/operations/combine-pdf) | `CombinePdf` | Merge PDFs / page ranges into one |
| [`generate()`](/operations/document-generation) | `DocumentGeneration` | Merge JSON data into a Word template |
| [`html()`](/operations/html-to-pdf) | `HtmlToPdf` | Convert a URL or HTML bundle to PDF |
| [`watermark()`](/operations/watermark) | `Watermark` | Stamp one PDF over another |

## Terminal methods

Every builder shares these:

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `dispatch()` | `AdobePdfProcess` | Queue the operation (async). Returns a `pending` process. |
| `dispatchSync()` | `AdobePdfProcess` | Run inline and return the finished process. |
| `toJob()` | `ProcessAdobePdfOperation` | Persist the process and return the job for `Bus::chain()` / `Bus::batch()`. |

## Shared configuration

Every builder also supports queue selection:

```php
LaravelAdobePdf::compress('scan.pdf')
    ->onConnection('redis')
    ->onQueue('pdfs')
    ->dispatch();
```

## Inputs

Operation inputs accept either a **path string** or a
[`Source`](#the-source-helper) object.

```php
LaravelAdobePdf::create('/absolute/path/report.docx')->dispatch();
LaravelAdobePdf::create('relative/path/report.docx')->dispatch();
```

### The `Source` helper

For inputs that aren't a plain local path, use `Source`:

```php
use ArielMejiaDev\LaravelAdobePdf\Support\Source;

Source::path('/tmp/a.docx');                 // a local filesystem path
Source::disk('s3', 'contracts/a.docx');      // a file on a Laravel disk
Source::contents($bytes, 'a.docx');          // raw in-memory bytes
Source::url('https://example.com');          // a remote URL (HTML to PDF)
```

The media type is inferred from the file extension; pass a third argument to override
it. Inputs are staged onto the storage disk when you dispatch, so the queued job can
read them from any worker.
