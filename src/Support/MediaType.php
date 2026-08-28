<?php

namespace ArielMejiaDev\LaravelAdobePdf\Support;

use Illuminate\Support\Str;

/**
 * Maps file extensions to the media types Adobe PDF Services expects.
 */
final class MediaType
{
    public const PDF = 'application/pdf';

    /**
     * @var array<string, string>
     */
    private const MAP = [
        'pdf' => self::PDF,
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain',
        'rtf' => 'application/rtf',
        'html' => 'text/html',
        'htm' => 'text/html',
        'zip' => 'application/zip',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'bmp' => 'image/bmp',
    ];

    public static function fromPath(string $path): string
    {
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        return self::MAP[$extension] ?? self::PDF;
    }

    public static function forExtension(string $extension): string
    {
        return self::MAP[Str::lower(ltrim($extension, '.'))] ?? self::PDF;
    }
}
