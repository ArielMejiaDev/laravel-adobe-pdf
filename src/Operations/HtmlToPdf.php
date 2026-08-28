<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;
use Illuminate\Support\Str;

/**
 * Convert HTML (a URL, an .html file, or a zip of HTML assets) into a PDF.
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#tag/Html-to-PDF
 */
class HtmlToPdf extends Operation
{
    public function __construct(string|Source|null $input = null)
    {
        if ($input instanceof Source) {
            $this->addInput($input);
        } elseif (is_string($input) && Str::startsWith($input, ['http://', 'https://'])) {
            $this->url($input);
        } elseif ($input !== null) {
            $this->addInput($input);
        }
    }

    public static function key(): string
    {
        return 'html';
    }

    public function endpoint(): string
    {
        return 'htmltopdf';
    }

    public function url(string $url): static
    {
        return $this->withOption('inputUrl', $url);
    }

    public function file(string|Source $input): static
    {
        return $this->addInput($input);
    }

    /**
     * Data merged into a dynamic HTML template (Adobe's "json" parameter).
     *
     * @param  array<string, mixed>  $data
     */
    public function data(array $data): static
    {
        return $this->withOption('json', $data);
    }

    public function pageSize(float $width, float $height): static
    {
        return $this->withOption('pageLayout', ['pageWidth' => $width, 'pageHeight' => $height]);
    }

    public function withHeaderFooter(bool $enabled = true): static
    {
        return $this->withOption('includeHeaderFooter', $enabled);
    }

    public function buildPayload(array $assetIds): array
    {
        $payload = [];

        if (($assetIds[0] ?? null) !== null) {
            $payload['assetID'] = $assetIds[0];
        } elseif (! empty($this->options['inputUrl'])) {
            $payload['inputUrl'] = $this->options['inputUrl'];
        }

        // Adobe expects "json" as a stringified JSON object (may be empty).
        $payload['json'] = json_encode($this->options['json'] ?? (object) []);

        if (! empty($this->options['pageLayout'])) {
            $payload['pageLayout'] = $this->options['pageLayout'];
        }

        if (array_key_exists('includeHeaderFooter', $this->options)) {
            $payload['includeHeaderFooter'] = $this->options['includeHeaderFooter'];
        }

        return $payload;
    }
}
