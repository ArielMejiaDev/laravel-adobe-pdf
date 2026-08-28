# HTML to PDF

Convert HTML to a PDF — from a URL, a single `.html` file, or a zipped bundle of HTML
and its assets.

> Adobe endpoint: `htmltopdf` · [reference](https://developer.adobe.com/document-services/docs/apis/#tag/Html-to-PDF)

## From a URL

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::html('https://example.com')->dispatch();
```

A string starting with `http://` or `https://` is treated as a URL — no upload happens.

## From a file or bundle

```php
LaravelAdobePdf::html('newsletter.zip')->dispatch();

// or explicitly
LaravelAdobePdf::html()->file('page.html')->dispatch();
```

## Methods

| Method | Description |
| ------ | ----------- |
| `url(string $url)` | Convert a remote URL. |
| `file(string\|Source $input)` | Convert an uploaded `.html` file or `.zip` bundle. |
| `data(array $data)` | Data for dynamic HTML (Adobe's `json` parameter). |
| `pageSize(float $width, float $height)` | Page size in inches. |
| `withHeaderFooter(bool $enabled = true)` | Include the default header/footer. |

```php
LaravelAdobePdf::html('https://example.com/report')
    ->data(['title' => 'Q3 Report'])
    ->pageSize(8.5, 11)
    ->withHeaderFooter()
    ->dispatch();
```

## Result

A PDF, stored as `output.pdf` on the process.

## Request payload

```json
{
  "inputUrl": "https://example.com/report",
  "json": "{\"title\":\"Q3 Report\"}",
  "pageLayout": { "pageWidth": 8.5, "pageHeight": 11 },
  "includeHeaderFooter": true
}
```

When you provide a file instead of a URL, `inputUrl` is replaced by the uploaded
`assetID`.
