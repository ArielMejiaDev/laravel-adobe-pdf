<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;
use Illuminate\Support\Str;

/**
 * Merge JSON data into a Word template to produce a PDF (or Word) document.
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#tag/Document-Generation
 */
class DocumentGeneration extends Operation
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(string|Source|null $template = null, array $data = [])
    {
        if ($template !== null) {
            $this->addInput($template);
        }

        if ($data !== []) {
            $this->data($data);
        }
    }

    public static function key(): string
    {
        return 'generate';
    }

    public function endpoint(): string
    {
        return 'documentgeneration';
    }

    public function template(string|Source $template): static
    {
        return $this->addInput($template);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function data(array $data): static
    {
        return $this->withOption('jsonDataForMerge', $data);
    }

    /**
     * @param  string  $format  "pdf" or "docx".
     */
    public function outputFormat(string $format): static
    {
        return $this->withOption('outputFormat', Str::lower($format));
    }

    public function asWord(): static
    {
        return $this->outputFormat('docx');
    }

    public function outputExtension(): string
    {
        return $this->options['outputFormat'] ?? 'pdf';
    }

    public function buildPayload(array $assetIds): array
    {
        return array_filter([
            'assetID' => $assetIds[0] ?? null,
            'outputFormat' => $this->options['outputFormat'] ?? 'pdf',
            'jsonDataForMerge' => $this->options['jsonDataForMerge'] ?? [],
        ], fn ($value) => $value !== null);
    }
}
