<?php

namespace ArielMejiaDev\LaravelAdobePdf\Models;

use ArielMejiaDev\LaravelAdobePdf\Database\Factories\AdobePdfProcessFactory;
use ArielMejiaDev\LaravelAdobePdf\Enums\ProcessStatus;
use ArielMejiaDev\LaravelAdobePdf\Support\OperationResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * A single Adobe PDF operation and its lifecycle.
 *
 * @property string $uuid
 * @property string $operation
 * @property ProcessStatus $status
 * @property array<string, mixed>|null $options
 * @property array<int, array<string, string>>|null $inputs
 * @property array<int, string>|null $asset_ids
 * @property string|null $adobe_location
 * @property int $poll_attempts
 * @property string|null $output_disk
 * @property string|null $output_path
 * @property array<string, mixed>|null $error
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $failed_at
 */
class AdobePdfProcess extends Model
{
    /** @use HasFactory<AdobePdfProcessFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => ProcessStatus::class,
        'options' => 'array',
        'inputs' => 'array',
        'asset_ids' => 'array',
        'error' => 'array',
        'poll_attempts' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('adobe-pdf.tracking.table', 'adobe_pdf_processes');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function newFactory(): AdobePdfProcessFactory
    {
        return AdobePdfProcessFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    public function hasFailed(): bool
    {
        return $this->status === ProcessStatus::Failed;
    }

    public function markUploading(): void
    {
        $this->forceFill([
            'status' => ProcessStatus::Uploading,
            'started_at' => $this->started_at ?? now(),
        ])->save();
    }

    public function markProcessing(?string $location = null): void
    {
        $this->forceFill(array_filter([
            'status' => ProcessStatus::Processing,
            'adobe_location' => $location,
        ], fn ($value) => $value !== null))->save();
    }

    public function markCompleted(string $disk, string $path): void
    {
        $this->forceFill([
            'status' => ProcessStatus::Completed,
            'output_disk' => $disk,
            'output_path' => $path,
            'completed_at' => now(),
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $error
     */
    public function markFailed(array $error): void
    {
        $this->forceFill([
            'status' => ProcessStatus::Failed,
            'error' => $error,
            'failed_at' => now(),
        ])->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */

    /**
     * Read the resulting document's bytes, or null if not completed yet.
     */
    public function output(): ?string
    {
        if ($this->output_disk === null || $this->output_path === null) {
            return null;
        }

        return Storage::disk($this->output_disk)->get($this->output_path);
    }

    public function outputUrl(): ?string
    {
        if ($this->output_disk === null || $this->output_path === null) {
            return null;
        }

        return Storage::disk($this->output_disk)->url($this->output_path);
    }

    public function recordResult(OperationResult $result): void
    {
        // Kept for callers that want to persist the raw Adobe payload alongside
        // the process without changing its terminal state.
        $this->forceFill(['options' => array_merge($this->options ?? [], [
            'result' => $result->raw,
        ])])->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param  Builder<AdobePdfProcess>  $query
     */
    public function scopeRunning(Builder $query): void
    {
        $query->whereIn('status', [
            ProcessStatus::Pending->value,
            ProcessStatus::Uploading->value,
            ProcessStatus::Processing->value,
        ]);
    }

    /**
     * @param  Builder<AdobePdfProcess>  $query
     */
    public function scopeSuccessful(Builder $query): void
    {
        $query->where('status', ProcessStatus::Completed->value);
    }

    /**
     * @param  Builder<AdobePdfProcess>  $query
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('status', ProcessStatus::Failed->value);
    }
}
