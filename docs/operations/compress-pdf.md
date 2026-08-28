# Compress PDF

Reduce the file size of a PDF.

> Adobe endpoint: `compresspdf` · [reference](https://developer.adobe.com/document-services/docs/apis/#tag/Compress-PDF)

## Basic usage

```php
use ArielMejiaDev\LaravelAdobePdf\Facades\LaravelAdobePdf;

LaravelAdobePdf::compress('scan.pdf')->dispatch();
```

## Methods

| Method | Description |
| ------ | ----------- |
| `from(string\|Source $input)` | Set the source PDF. |
| `level(string $level)` | Compression level: `LOW`, `MEDIUM` (default) or `HIGH`. |

The level is case-insensitive — `->level('high')` and `->level('HIGH')` are equivalent.
Convenience constants are available too:

```php
use ArielMejiaDev\LaravelAdobePdf\Operations\CompressPdf;

LaravelAdobePdf::compress('scan.pdf')
    ->level(CompressPdf::HIGH)
    ->dispatch();
```

## Result

A compressed PDF, stored as `output.pdf` on the process.

## Request payload

```json
{
  "assetID": "urn:aaid:AS:UE1:...",
  "compressionLevel": "HIGH"
}
```
