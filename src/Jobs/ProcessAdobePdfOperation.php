<?php

namespace ArielMejiaDev\LaravelAdobePdf\Jobs;

use ArielMejiaDev\LaravelAdobePdf\Client\AdobePdfClient;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\AdobePdfException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\OperationFailedException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\RateLimitException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\TimeoutException;
use ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess;
use ArielMejiaDev\LaravelAdobePdf\Operations\OperationRegistry;
use ArielMejiaDev\LaravelAdobePdf\Support\OperationResult;
use ArielMejiaDev\LaravelAdobePdf\Support\Source;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Drives a single Adobe operation through its whole lifecycle:
 *
 *   upload inputs  ->  submit operation  ->  poll until done  ->  download result
 *
 * Each step is idempotent and reads its state back from the process record, so
 * the job can be released back onto the queue between polls without ever
 * blocking a worker in a sleep() loop. When run synchronously (sync connection
 * or invoked directly), it instead polls in a bounded in-process loop.
 */
class ProcessAdobePdfOperation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $processId) {}

    /**
     * Bound total retries by time so a stuck job cannot loop forever.
     */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addSeconds(($this->pollInterval() * $this->maxPollAttempts()) + 120);
    }

    public function handle(AdobePdfClient $client, OperationRegistry $registry): void
    {
        $process = AdobePdfProcess::find($this->processId);

        if ($process === null || $process->isFinished()) {
            return;
        }

        try {
            $this->ensureUploaded($process, $client);
            $this->ensureSubmitted($process, $client, $registry);
            $this->awaitCompletion($process, $client, $registry);
        } catch (TimeoutException|OperationFailedException) {
            // Definitive failure: the process is already marked failed at the
            // throw site. Never retry it — just stop.
        } catch (RateLimitException $exception) {
            if ($this->runningSync()) {
                $process->markFailed($this->errorContext($exception));

                return;
            }

            // Back off and try again later without counting as a failure.
            $this->release($exception->retryAfter ?? ($this->pollInterval() * 2));
        } catch (AdobePdfException $exception) {
            if ($this->runningSync()) {
                $process->markFailed($this->errorContext($exception));

                return;
            }

            // Async: let the queue retry (bounded by retryUntil()); failed()
            // records the failure on the process once retries are exhausted.
            throw $exception;
        }
    }

    /**
     * Upload every non-remote input and record the resulting asset IDs.
     */
    protected function ensureUploaded(AdobePdfProcess $process, AdobePdfClient $client): void
    {
        if ($process->asset_ids !== null) {
            return;
        }

        $process->markUploading();

        $assetIds = [];

        foreach ($process->inputs ?? [] as $descriptor) {
            if (($descriptor['kind'] ?? null) === 'url') {
                continue;
            }

            $source = Source::fromDescriptor($descriptor);
            $asset = $client->uploadContents($source->read(), $descriptor['mediaType']);

            $assetIds[] = $asset->assetID;
        }

        $process->forceFill(['asset_ids' => $assetIds])->save();
    }

    /**
     * Submit the operation to Adobe and store the status polling location.
     */
    protected function ensureSubmitted(AdobePdfProcess $process, AdobePdfClient $client, OperationRegistry $registry): void
    {
        if ($process->adobe_location !== null) {
            return;
        }

        $operation = $registry->rehydrate($process->operation, $process->options ?? []);
        $payload = $operation->buildPayload($process->asset_ids ?? []);

        $location = $client->submit($operation->endpoint(), $payload);

        $process->markProcessing($location);
    }

    /**
     * Poll for completion, releasing the job (async) or looping (sync).
     */
    protected function awaitCompletion(AdobePdfProcess $process, AdobePdfClient $client, OperationRegistry $registry): void
    {
        do {
            $result = $client->poll((string) $process->adobe_location);

            if ($result->isFailed()) {
                $process->markFailed($this->errorContext(OperationFailedException::fromStatus($result->raw)));

                throw OperationFailedException::fromStatus($result->raw);
            }

            if ($result->isDone()) {
                $this->store($process, $client, $registry, $result);

                return;
            }

            $process->increment('poll_attempts');

            if ($process->poll_attempts >= $this->maxPollAttempts()) {
                $process->markFailed(['code' => 'TIMEOUT', 'message' => 'Polling timed out.']);

                throw TimeoutException::afterAttempts($process->poll_attempts);
            }

            if (! $this->runningSync()) {
                $this->release($this->pollInterval());

                return;
            }

            sleep($this->pollInterval());
        } while (true);
    }

    /**
     * Download the finished document and mark the process complete.
     */
    protected function store(AdobePdfProcess $process, AdobePdfClient $client, OperationRegistry $registry, OperationResult $result): void
    {
        $contents = $client->download((string) $result->downloadUri);

        $operation = $registry->rehydrate($process->operation, $process->options ?? []);

        $disk = (string) config('adobe-pdf.storage.disk', 'local');
        $base = trim((string) config('adobe-pdf.storage.path', 'adobe-pdf'), '/');
        $path = $base.'/'.$process->uuid.'/output.'.$operation->outputExtension();

        Storage::disk($disk)->put($path, $contents);

        $process->markCompleted($disk, $path);
    }

    public function failed(?Throwable $exception): void
    {
        $process = AdobePdfProcess::find($this->processId);

        if ($process !== null && ! $process->isFinished()) {
            $process->markFailed([
                'code' => 'JOB_FAILED',
                'message' => $exception?->getMessage() ?? 'The operation job failed.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function errorContext(AdobePdfException $exception): array
    {
        return [
            'code' => $exception->errorCode,
            'message' => $exception->getMessage(),
            'status' => $exception->status,
            'context' => $exception->context,
        ];
    }

    protected function runningSync(): bool
    {
        return $this->job === null || $this->job->getConnectionName() === 'sync';
    }

    protected function pollInterval(): int
    {
        return (int) config('adobe-pdf.polling.backoff', 5);
    }

    protected function maxPollAttempts(): int
    {
        return (int) config('adobe-pdf.polling.max_attempts', 60);
    }
}
