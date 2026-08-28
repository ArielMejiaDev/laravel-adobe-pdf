<?php

namespace ArielMejiaDev\LaravelAdobePdf\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * A normalized input for an operation.
 *
 * A source can be raw contents, a local filesystem path, a file already living
 * on a Laravel disk, or (for HTML to PDF) a remote URL. Uploadable sources are
 * "staged" onto the package storage disk at dispatch time so the queued job can
 * read them regardless of which worker picks it up.
 */
final class Source
{
    private function __construct(
        public readonly string $kind,
        public readonly string $mediaType,
        public readonly string $filename,
        public readonly ?string $contents = null,
        public readonly ?string $path = null,
        public readonly ?string $disk = null,
        public readonly ?string $url = null,
    ) {}

    public static function make(string|Source $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return Str::startsWith($value, ['http://', 'https://'])
            ? self::url($value)
            : self::path($value);
    }

    public static function path(string $path, ?string $mediaType = null): self
    {
        return new self(
            kind: 'localPath',
            mediaType: $mediaType ?? MediaType::fromPath($path),
            filename: basename($path),
            path: $path,
        );
    }

    public static function disk(string $disk, string $path, ?string $mediaType = null): self
    {
        return new self(
            kind: 'diskPath',
            mediaType: $mediaType ?? MediaType::fromPath($path),
            filename: basename($path),
            path: $path,
            disk: $disk,
        );
    }

    public static function contents(string $contents, string $filename, ?string $mediaType = null): self
    {
        return new self(
            kind: 'contents',
            mediaType: $mediaType ?? MediaType::fromPath($filename),
            filename: $filename,
            contents: $contents,
        );
    }

    public static function url(string $url): self
    {
        return new self(
            kind: 'url',
            mediaType: MediaType::forExtension('html'),
            filename: basename(parse_url($url, PHP_URL_PATH) ?: 'index.html'),
            url: $url,
        );
    }

    public function isRemote(): bool
    {
        return $this->kind === 'url';
    }

    /**
     * Read the raw bytes of the source (not valid for remote URLs).
     */
    public function read(): string
    {
        return match ($this->kind) {
            'contents' => (string) $this->contents,
            'localPath' => File::get((string) $this->path),
            'diskPath' => (string) Storage::disk((string) $this->disk)->get((string) $this->path),
            default => throw new InvalidArgumentException("Source of kind [{$this->kind}] cannot be read."),
        };
    }

    /**
     * Persist this source onto the package storage disk and return the
     * descriptor that will be stored on the process and read back by the job.
     *
     * @return array<string, string>
     */
    public function stage(string $disk, string $folder): array
    {
        if ($this->isRemote()) {
            return [
                'kind' => 'url',
                'url' => (string) $this->url,
                'mediaType' => $this->mediaType,
                'filename' => $this->filename,
            ];
        }

        $path = $folder.'/'.Str::random(20).'-'.$this->filename;

        Storage::disk($disk)->put($path, $this->read());

        return [
            'kind' => 'diskPath',
            'disk' => $disk,
            'path' => $path,
            'mediaType' => $this->mediaType,
            'filename' => $this->filename,
        ];
    }

    /**
     * @param  array<string, string>  $descriptor
     */
    public static function fromDescriptor(array $descriptor): self
    {
        return match ($descriptor['kind']) {
            'url' => self::url($descriptor['url']),
            default => self::disk($descriptor['disk'], $descriptor['path'], $descriptor['mediaType']),
        };
    }
}
