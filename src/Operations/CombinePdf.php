<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;

/**
 * Combine several PDFs (or page ranges of them) into a single PDF.
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#tag/Combine-PDF
 */
class CombinePdf extends Operation
{
    /**
     * @param  array<int, string|Source>  $inputs
     */
    public function __construct(array $inputs = [])
    {
        foreach ($inputs as $input) {
            $this->add($input);
        }
    }

    public static function key(): string
    {
        return 'combine';
    }

    public function endpoint(): string
    {
        return 'combinepdf';
    }

    /**
     * Add a file to the combine list, optionally limited to page ranges.
     *
     * @param  array<int, array{0: int, 1: int}|array{start: int, end: int}>  $pageRanges
     */
    public function add(string|Source $input, array $pageRanges = []): static
    {
        $this->addInput($input);

        $ranges = $this->options['pageRanges'] ?? [];
        $ranges[] = $pageRanges;
        $this->options['pageRanges'] = $ranges;

        return $this;
    }

    public function buildPayload(array $assetIds): array
    {
        $assets = [];

        foreach ($assetIds as $index => $assetId) {
            $entry = ['assetID' => $assetId];

            $ranges = $this->normalizeRanges($this->options['pageRanges'][$index] ?? []);

            if ($ranges !== []) {
                $entry['pageRanges'] = $ranges;
            }

            $assets[] = $entry;
        }

        return ['assets' => $assets];
    }

    /**
     * @param  array<int, array{0: int, 1: int}|array{start: int, end: int}>  $ranges
     * @return array<int, array{start: int, end: int}>
     */
    protected function normalizeRanges(array $ranges): array
    {
        return array_map(function (array $range): array {
            if (array_key_exists('start', $range)) {
                return ['start' => (int) $range['start'], 'end' => (int) $range['end']];
            }

            return ['start' => (int) $range[0], 'end' => (int) $range[1]];
        }, $ranges);
    }
}
