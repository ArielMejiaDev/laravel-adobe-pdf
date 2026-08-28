<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Support\Source;
use Illuminate\Support\Str;

/**
 * Reduce the file size of a PDF.
 *
 * @see https://developer.adobe.com/document-services/docs/apis/#tag/Compress-PDF
 */
class CompressPdf extends Operation
{
    public const LOW = 'LOW';

    public const MEDIUM = 'MEDIUM';

    public const HIGH = 'HIGH';

    public function __construct(string|Source|null $input = null)
    {
        if ($input !== null) {
            $this->addInput($input);
        }
    }

    public static function key(): string
    {
        return 'compress';
    }

    public function endpoint(): string
    {
        return 'compresspdf';
    }

    public function from(string|Source $input): static
    {
        return $this->addInput($input);
    }

    /**
     * @param  string  $level  One of LOW, MEDIUM, HIGH.
     */
    public function level(string $level): static
    {
        return $this->withOption('compressionLevel', Str::upper($level));
    }

    public function buildPayload(array $assetIds): array
    {
        return array_filter([
            'assetID' => $assetIds[0] ?? null,
            'compressionLevel' => $this->options['compressionLevel'] ?? self::MEDIUM,
        ], fn ($value) => $value !== null);
    }
}
