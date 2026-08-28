<?php

namespace ArielMejiaDev\LaravelAdobePdf\Support;

/**
 * The decoded body of a job status poll.
 *
 * Adobe reports "status" as one of "in progress", "done" or "failed". When done,
 * the resulting file is referenced by a presigned "downloadUri" that lives under
 * one of a few keys depending on the operation (asset / content / resource).
 */
final class OperationResult
{
    public const IN_PROGRESS = 'in progress';

    public const DONE = 'done';

    public const FAILED = 'failed';

    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly string $status,
        public readonly ?string $downloadUri = null,
        public readonly ?string $assetID = null,
        public readonly array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromResponse(array $data): self
    {
        $asset = $data['asset'] ?? $data['content'] ?? $data['resource'] ?? [];

        return new self(
            status: $data['status'] ?? self::IN_PROGRESS,
            downloadUri: is_array($asset) ? ($asset['downloadUri'] ?? null) : null,
            assetID: is_array($asset) ? ($asset['assetID'] ?? null) : null,
            raw: $data,
        );
    }

    public function isDone(): bool
    {
        return $this->status === self::DONE;
    }

    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    public function inProgress(): bool
    {
        return ! $this->isDone() && ! $this->isFailed();
    }
}
