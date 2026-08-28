<?php

namespace ArielMejiaDev\LaravelAdobePdf\Operations;

use ArielMejiaDev\LaravelAdobePdf\Enums\ProcessStatus;
use ArielMejiaDev\LaravelAdobePdf\Jobs\ProcessAdobePdfOperation;
use ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess;
use Illuminate\Support\Str;

/**
 * Turns a configured {@see Operation} into a persisted process and a queued job.
 *
 * Input files are staged onto the package storage disk here so that whichever
 * worker later runs the job can read them, regardless of where it dispatched.
 */
class OperationDispatcher
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config) {}

    public function dispatch(Operation $operation): AdobePdfProcess
    {
        $process = $this->makeProcess($operation);

        dispatch($this->job($operation, $process));

        return $process;
    }

    public function dispatchSync(Operation $operation): AdobePdfProcess
    {
        $process = $this->makeProcess($operation);

        dispatch_sync($this->job($operation, $process));

        return $process->refresh();
    }

    public function toJob(Operation $operation): ProcessAdobePdfOperation
    {
        return $this->job($operation, $this->makeProcess($operation));
    }

    /**
     * Create the tracking record and stage every input file.
     */
    public function makeProcess(Operation $operation): AdobePdfProcess
    {
        $uuid = (string) Str::uuid();

        $disk = (string) ($this->config['storage']['disk'] ?? 'local');
        $folder = trim((string) ($this->config['storage']['path'] ?? 'adobe-pdf'), '/').'/'.$uuid.'/inputs';

        $descriptors = array_map(
            fn ($source) => $source->stage($disk, $folder),
            $operation->inputs(),
        );

        return AdobePdfProcess::create([
            'uuid' => $uuid,
            'operation' => $operation::key(),
            'status' => ProcessStatus::Pending,
            'options' => $operation->options(),
            'inputs' => array_values($descriptors),
        ]);
    }

    protected function job(Operation $operation, AdobePdfProcess $process): ProcessAdobePdfOperation
    {
        $job = new ProcessAdobePdfOperation($process->getKey());

        $connection = $operation->connection() ?? ($this->config['queue']['connection'] ?? null);
        $queue = $operation->queue() ?? ($this->config['queue']['queue'] ?? null);

        if ($connection !== null) {
            $job->onConnection($connection);
        }

        if ($queue !== null) {
            $job->onQueue($queue);
        }

        return $job;
    }
}
