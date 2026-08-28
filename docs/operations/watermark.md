# Watermark

Stamp a watermark PDF over an existing PDF document.

> Adobe endpoint: `addwatermark` · [reference](https://developer.adobe.com/document-services/docs/apis/#tag/PDF-Watermark)

## Basic usage

Pass the **document** to stamp and the **watermark** PDF as the two arguments of the
`watermark()` entry point, in that order:

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

// LaravelAdobePdf::watermark($document, $watermark)
LaravelAdobePdf::watermark('report.pdf', 'confidential.pdf')->dispatch();
```

### Setting the files fluently

The same two files can be set with the `document()` and `watermark()` builder methods
instead of positional arguments — useful when you build the operation up gradually.
Note that the `watermark()` **entry point** (on the facade) and the `watermark()`
**builder method** are distinct: the entry point creates the operation, the builder
method sets the watermark file.

```php
LaravelAdobePdf::watermark()          // no arguments here
    ->document('report.pdf')          // the PDF to stamp
    ->watermark('confidential.pdf')   // the watermark PDF
    ->dispatch();
```

The document is always uploaded first, so its asset order is preserved regardless of
which style you use.

## Methods

| Method | Description |
| ------ | ----------- |
| `document(string\|Source $document)` | The PDF to watermark. |
| `watermark(string\|Source $watermark)` | The watermark PDF. |
| `opacity(int $opacity)` | Watermark opacity, `0`–`100`. |
| `onForeground(bool $foreground = true)` | Draw over (`true`) or under (`false`) the content. |
| `pages(array $pageRanges)` | Limit to page ranges (`[[start, end]]`). |

```php
LaravelAdobePdf::watermark('report.pdf', 'draft.pdf')
    ->opacity(40)
    ->onForeground()
    ->pages([[1, 1], [5, 8]])
    ->dispatch();
```

## Result

The watermarked PDF, stored as `output.pdf` on the process.

## Request payload

```json
{
  "inputDocumentAssetID": "urn:...:doc",
  "watermarkDocumentAssetID": "urn:...:mark",
  "appearance": { "opacity": 40, "appearOnForeground": true },
  "pageRanges": [{ "start": 1, "end": 1 }, { "start": 5, "end": 8 }]
}
```
