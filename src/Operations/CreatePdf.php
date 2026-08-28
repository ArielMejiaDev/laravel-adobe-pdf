<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;

/**
 * Create a PDF from a non-PDF document (Word, Excel, PowerPoint, text, images…).
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#tag/Create-PDF
 */
class CreatePdf extends Operation
{
    public function __construct(string|Source|null $input = null)
    {
        if ($input !== null) {
            $this->addInput($input);
        }
    }

    public static function key(): string
    {
        return 'create';
    }

    public function endpoint(): string
    {
        return 'createpdf';
    }

    public function from(string|Source $input): static
    {
        return $this->addInput($input);
    }

    /**
     * The language of the source document, used for better OCR/layout results.
     */
    public function language(string $language): static
    {
        return $this->withOption('documentLanguage', $language);
    }

    public function buildPayload(array $assetIds): array
    {
        return array_filter([
            'assetID' => $assetIds[0] ?? null,
            'documentLanguage' => $this->options['documentLanguage'] ?? null,
        ], fn ($value) => $value !== null);
    }
}
