<?php

namespace ArielMejiaDev\LaravelAdobePdf\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\CreatePdf create(string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $input = null)
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\ExtractPdf extract(string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $input = null)
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\CompressPdf compress(string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $input = null)
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\CombinePdf combine(array $inputs = [])
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\DocumentGeneration generate(string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $template = null, array $data = [])
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\HtmlToPdf html(string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $input = null)
 * @method static \ArielMejiaDev\LaravelAdobePdf\Operations\Watermark watermark(string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $document = null, string|\ArielMejiaDev\LaravelAdobePdf\Support\Source|null $watermark = null)
 * @method static \ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess|null process(string $uuid)
 * @method static \Illuminate\Database\Eloquent\Builder processes()
 *
 * @see \ArielMejiaDev\LaravelAdobePdf\LaravelAdobePdf
 */
class LaravelAdobePdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ArielMejiaDev\LaravelAdobePdf\LaravelAdobePdf::class;
    }
}
