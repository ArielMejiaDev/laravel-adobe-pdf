<?php

namespace ArielMejiaDev\LaravelAdobePdf;

use ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess;
use ArielMejiaDev\LaravelAdobePdf\Operations\CombinePdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\CompressPdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\CreatePdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\DocumentGeneration;
use ArielMejiaDev\LaravelAdobePdf\Operations\ExtractPdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\HtmlToPdf;
use ArielMejiaDev\LaravelAdobePdf\Operations\Watermark;
use ArielMejiaDev\LaravelAdobePdf\Support\Source;
use Illuminate\Database\Eloquent\Builder;

/**
 * Entry point for the fluent Adobe PDF Services API.
 *
 * Every method returns a fluent operation builder you then dispatch:
 *
 *     LaravelAdobePdf::extract('invoice.pdf')->tables()->text()->dispatch();
 *     LaravelAdobePdf::compress($pdf)->level('HIGH')->dispatchSync();
 */
class LaravelAdobePdf
{
    public function create(string|Source|null $input = null): CreatePdf
    {
        return new CreatePdf($input);
    }

    public function extract(string|Source|null $input = null): ExtractPdf
    {
        return new ExtractPdf($input);
    }

    public function compress(string|Source|null $input = null): CompressPdf
    {
        return new CompressPdf($input);
    }

    /**
     * @param  array<int, string|Source>  $inputs
     */
    public function combine(array $inputs = []): CombinePdf
    {
        return new CombinePdf($inputs);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function generate(string|Source|null $template = null, array $data = []): DocumentGeneration
    {
        return new DocumentGeneration($template, $data);
    }

    public function html(string|Source|null $input = null): HtmlToPdf
    {
        return new HtmlToPdf($input);
    }

    public function watermark(string|Source|null $document = null, string|Source|null $watermark = null): Watermark
    {
        return new Watermark($document, $watermark);
    }

    /**
     * Look up a tracked process by its public UUID.
     */
    public function process(string $uuid): ?AdobePdfProcess
    {
        return AdobePdfProcess::query()->where('uuid', $uuid)->first();
    }

    /**
     * Query the tracked processes (e.g. for a dashboard).
     *
     * @return Builder<AdobePdfProcess>
     */
    public function processes(): Builder
    {
        return AdobePdfProcess::query();
    }
}
