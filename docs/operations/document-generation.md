# Document Generation

Merge JSON data into a Word (`.docx`) template that uses
[document generation tags](https://developer.adobe.com/document-services/docs/overview/document-generation-api/),
producing a PDF (or Word) document.

> Adobe endpoint: `documentgeneration` · [reference](https://developer.adobe.com/document-services/docs/apis/#tag/Document-Generation)

## Basic usage

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::generate('invoice-template.docx', [
    'customer' => 'Ada Lovelace',
    'total' => 1280.50,
    'items' => [
        ['name' => 'Analytical Engine', 'price' => 1280.50],
    ],
])->dispatch();
```

## Methods

| Method | Description |
| ------ | ----------- |
| `template(string\|Source $template)` | Set the `.docx` template. |
| `data(array $data)` | The JSON data merged into the template. |
| `outputFormat(string $format)` | `pdf` (default) or `docx`. |
| `asWord()` | Shortcut for `outputFormat('docx')`. |

The template and data can be passed straight to the factory:

```php
LaravelAdobePdf::generate('template.docx', ['name' => 'Ada'])
    ->asWord()
    ->dispatch();
```

## Result

A PDF (`output.pdf`) or Word document (`output.docx`) depending on `outputFormat`.

## Request payload

```json
{
  "assetID": "urn:aaid:AS:UE1:...",
  "outputFormat": "pdf",
  "jsonDataForMerge": {
    "customer": "Ada Lovelace",
    "total": 1280.5
  }
}
```
