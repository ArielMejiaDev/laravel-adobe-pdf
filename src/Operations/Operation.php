<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Jobs\ProcessAdobePdfOperation;
use ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess;
use ArielMejiaDev\LaravelAdobePdf\Support\MediaType;
use ArielMejiaDev\LaravelAdobePdf\Support\Source;

/**
 * Base class for every fluent operation builder.
 *
 * An operation is two things at once:
 *   1. a fluent builder the developer configures, then dispatches;
 *   2. a definition of how to turn uploaded asset IDs + options into the JSON
 *      body Adobe expects (via {@see buildPayload()}), which the queued job
 *      reconstructs from the persisted state.
 *
 * Because payload building is a pure function of options + asset IDs, the job
 * only needs to persist the operation key and options — never the builder.
 */
abstract class Operation
{
    /** @var array<int, Source> */
    protected array $inputs = [];

    /** @var array<string, mixed> */
    protected array $options = [];

    protected ?string $connection = null;

    protected ?string $queue = null;

    /**
     * The operation key persisted on the process (e.g. "extract").
     */
    abstract public static function key(): string;

    /**
     * The Adobe operation endpoint segment (e.g. "extractpdf").
     */
    abstract public function endpoint(): string;

    /**
     * Build the JSON body sent to Adobe from the uploaded asset IDs.
     *
     * @param  array<int, string>  $assetIds
     * @return array<string, mixed>
     */
    abstract public function buildPayload(array $assetIds): array;

    /**
     * File extension used when storing the resulting document.
     */
    public function outputExtension(): string
    {
        return 'pdf';
    }

    /*
    |--------------------------------------------------------------------------
    | Fluent queue configuration
    |--------------------------------------------------------------------------
    */

    public function onQueue(?string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function onConnection(?string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Terminal actions
    |--------------------------------------------------------------------------
    */

    /**
     * Persist the process and push the job onto the queue.
     */
    public function dispatch(): AdobePdfProcess
    {
        return $this->dispatcher()->dispatch($this);
    }

    /**
     * Persist the process and run the job immediately (synchronously).
     */
    public function dispatchSync(): AdobePdfProcess
    {
        return $this->dispatcher()->dispatchSync($this);
    }

    /**
     * Persist the process and return the job instance for bus chaining.
     */
    public function toJob(): ProcessAdobePdfOperation
    {
        return $this->dispatcher()->toJob($this);
    }

    /*
    |--------------------------------------------------------------------------
    | State accessors (used by the dispatcher and job)
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<int, Source>
     */
    public function inputs(): array
    {
        return $this->inputs;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }

    public function connection(): ?string
    {
        return $this->connection;
    }

    public function queue(): ?string
    {
        return $this->queue;
    }

    /**
     * Rehydrate a fresh operation instance with persisted options.
     *
     * @param  array<string, mixed>  $options
     */
    public function hydrate(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers for subclasses
    |--------------------------------------------------------------------------
    */

    protected function addInput(string|Source $source, ?string $mediaType = null): static
    {
        $source = $source instanceof Source ? $source : Source::path($source, $mediaType);

        $this->inputs[] = $source;

        return $this;
    }

    protected function withOption(string $key, mixed $value): static
    {
        $this->options[$key] = $value;

        return $this;
    }

    protected function defaultMediaType(): string
    {
        return MediaType::PDF;
    }

    protected function dispatcher(): OperationDispatcher
    {
        return app(OperationDispatcher::class);
    }
}
