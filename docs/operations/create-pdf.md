# Create PDF

Create a PDF from a non-PDF document — Word, Excel, PowerPoint, text, RTF or images.

> Adobe endpoint: `createpdf` · [reference](https://developer.adobe.com/document-services/docs/apis/#tag/Create-PDF)

## Basic usage

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::create('contract.docx')->dispatch();
```

## Methods

| Method | Description |
| ------ | ----------- |
| `from(string\|Source $input)` | Set the source document. |
| `language(string $language)` | Document language (e.g. `en-US`) for better layout/OCR. |

You can also pass the input straight into the factory:

```php
LaravelAdobePdf::create('contract.docx')
    ->language('en-US')
    ->dispatch();
```

## Supported input types

`.doc`, `.docx`, `.ppt`, `.pptx`, `.xls`, `.xlsx`, `.txt`, `.rtf`, and common image
formats (`.png`, `.jpg`, `.gif`, `.tiff`, `.bmp`).

## Result

A single PDF, stored as `output.pdf` on the process. Read it with `$process->output()`.

## Request payload

```json
{
  "assetID": "urn:aaid:AS:UE1:...",
  "documentLanguage": "en-US"
}
```
