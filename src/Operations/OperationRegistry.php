<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use InvalidArgumentException;

/**
 * Resolves an operation key back into a fresh operation instance so the queued
 * job can rebuild the Adobe payload without serializing the original builder.
 */
class OperationRegistry
{
    /**
     * @var array<string, class-string<Operation>>
     */
    protected array $operations = [
        'create' => CreatePdf::class,
        'extract' => ExtractPdf::class,
        'compress' => CompressPdf::class,
        'combine' => CombinePdf::class,
        'generate' => DocumentGeneration::class,
        'html' => HtmlToPdf::class,
        'watermark' => Watermark::class,
    ];

    public function make(string $key): Operation
    {
        $class = $this->operations[$key] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("Unknown Adobe PDF operation [{$key}].");
        }

        return new $class;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function rehydrate(string $key, array $options): Operation
    {
        return $this->make($key)->hydrate($options);
    }

    /**
     * @param  class-string<Operation>  $class
     */
    public function register(string $key, string $class): void
    {
        $this->operations[$key] = $class;
    }
}
