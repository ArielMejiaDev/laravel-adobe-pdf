<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;

/**
 * Extract text, tables and figures from a PDF as structured JSON (+ renditions).
 *
 * The result is a ZIP containing "structuredData.json" and any requested
 * table/figure renditions.
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#operation/pdfoperations.extractpdf
 */
class ExtractPdf extends Operation
{
    public function __construct(string|Source|null $input = null)
    {
        if ($input !== null) {
            $this->addInput($input);
        }
    }

    public static function key(): string
    {
        return 'extract';
    }

    public function endpoint(): string
    {
        return 'extractpdf';
    }

    public function from(string|Source $input): static
    {
        return $this->addInput($input);
    }

    public function text(): static
    {
        return $this->addElement('text');
    }

    public function tables(): static
    {
        return $this->addElement('tables');
    }

    /**
     * @param  string  ...$types  One or more of: "tables", "figures".
     */
    public function renditions(string ...$types): static
    {
        return $this->withOption('renditionsToExtract', array_values(array_unique(
            array_merge($this->options['renditionsToExtract'] ?? [], $types)
        )));
    }

    /**
     * @param  string  $format  "csv" or "xlsx".
     */
    public function tableFormat(string $format): static
    {
        return $this->withOption('tableOutputFormat', $format);
    }

    public function withCharBounds(bool $enabled = true): static
    {
        return $this->withOption('getCharBounds', $enabled);
    }

    public function outputExtension(): string
    {
        return 'zip';
    }

    public function buildPayload(array $assetIds): array
    {
        return array_filter([
            'assetID' => $assetIds[0] ?? null,
            'elementsToExtract' => $this->options['elementsToExtract'] ?? ['text'],
            'renditionsToExtract' => $this->options['renditionsToExtract'] ?? null,
            'tableOutputFormat' => $this->options['tableOutputFormat'] ?? null,
            'getCharBounds' => $this->options['getCharBounds'] ?? null,
        ], fn ($value) => $value !== null);
    }

    protected function addElement(string $element): static
    {
        return $this->withOption('elementsToExtract', array_values(array_unique(
            array_merge($this->options['elementsToExtract'] ?? [], [$element])
        )));
    }
}
