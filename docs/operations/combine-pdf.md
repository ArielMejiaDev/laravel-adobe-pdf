# Combine PDF

Merge several PDFs — or specific page ranges of them — into a single PDF.

> Adobe endpoint: `combinepdf` · [reference](https://developer.adobe.com/document-services/docs/apis/#tag/Combine-PDF)

## Basic usage

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::combine()
    ->add('cover.pdf')
    ->add('body.pdf')
    ->add('appendix.pdf')
    ->dispatch();
```

You can also pass an array of inputs to the factory:

```php
LaravelAdobePdf::combine(['a.pdf', 'b.pdf', 'c.pdf'])->dispatch();
```

## Methods

| Method | Description |
| ------ | ----------- |
| `add(string\|Source $input, array $pageRanges = [])` | Append a file, optionally limited to page ranges. |

## Page ranges

Ranges are `[start, end]` pairs (1-based, inclusive). The order of files is preserved.

```php
LaravelAdobePdf::combine()
    ->add('report.pdf', [[1, 3]])       // pages 1–3
    ->add('extra.pdf', [[2, 4], [8, 8]]) // pages 2–4 and page 8
    ->add('back-cover.pdf')              // all pages
    ->dispatch();
```

You may also use the associative form `['start' => 1, 'end' => 3]`.

## Result

A single merged PDF, stored as `output.pdf` on the process.

## Request payload

```json
{
  "assets": [
    { "assetID": "urn:...:a", "pageRanges": [{ "start": 1, "end": 3 }] },
    { "assetID": "urn:...:b" }
  ]
}
```
