<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;

/**
 * Stamp a watermark PDF over an existing PDF document.
 *
 * The input document is asset 0 and the watermark document is asset 1, so their
 * order is preserved explicitly regardless of how the builder is called.
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#tag/PDF-Watermark
 */
class Watermark extends Operation
{
    protected ?Source $documentSource = null;

    protected ?Source $watermarkSource = null;

    public function __construct(string|Source|null $document = null, string|Source|null $watermark = null)
    {
        if ($document !== null) {
            $this->document($document);
        }

        if ($watermark !== null) {
            $this->watermark($watermark);
        }
    }

    public static function key(): string
    {
        return 'watermark';
    }

    public function endpoint(): string
    {
        return 'addwatermark';
    }

    public function document(string|Source $document): static
    {
        $this->documentSource = $document instanceof Source ? $document : Source::path($document);

        return $this;
    }

    public function watermark(string|Source $watermark): static
    {
        $this->watermarkSource = $watermark instanceof Source ? $watermark : Source::path($watermark);

        return $this;
    }

    public function opacity(int $opacity): static
    {
        $appearance = $this->options['appearance'] ?? [];
        $appearance['opacity'] = $opacity;

        return $this->withOption('appearance', $appearance);
    }

    public function onForeground(bool $foreground = true): static
    {
        $appearance = $this->options['appearance'] ?? [];
        $appearance['appearOnForeground'] = $foreground;

        return $this->withOption('appearance', $appearance);
    }

    /**
     * @param  array<int, array{0: int, 1: int}|array{start: int, end: int}>  $pageRanges
     */
    public function pages(array $pageRanges): static
    {
        return $this->withOption('pageRanges', array_map(function (array $range): array {
            if (array_key_exists('start', $range)) {
                return ['start' => (int) $range['start'], 'end' => (int) $range['end']];
            }

            return ['start' => (int) $range[0], 'end' => (int) $range[1]];
        }, $pageRanges));
    }

    public function inputs(): array
    {
        return array_values(array_filter([$this->documentSource, $this->watermarkSource]));
    }

    public function buildPayload(array $assetIds): array
    {
        return array_filter([
            'inputDocumentAssetID' => $assetIds[0] ?? null,
            'watermarkDocumentAssetID' => $assetIds[1] ?? null,
            'appearance' => $this->options['appearance'] ?? null,
            'pageRanges' => $this->options['pageRanges'] ?? null,
        ], fn ($value) => $value !== null);
    }
}
