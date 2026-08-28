<?php

namespace ArielMejiaDev\LaravelAdobePdf\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class AdobePdfException extends Exception
{
    /**
     * The Adobe error code (e.g. "INVALID_ASSET_ID"), when present.
     */
    public ?string $errorCode = null;

    /**
     * The HTTP status code returned by Adobe, when the error came from a call.
     */
    public ?int $status = null;

    /**
     * The raw decoded error payload returned by Adobe.
     *
     * @var array<string, mixed>
     */
    public array $context = [];

    /**
     * Build an exception from a failed Adobe HTTP response.
     *
     * Adobe returns errors shaped like:
     *   { "error": { "code": "...", "message": "..." } }
     * or, on some endpoints, a flat { "code": "...", "message": "..." }.
     */
    public static function fromResponse(Response $response): static
    {
        $body = $response->json() ?? [];
        $error = $body['error'] ?? $body;

        $code = $error['code'] ?? $error['status'] ?? null;
        $message = ($error['message'] ?? $error['title'] ?? $response->reason()) ?: 'Adobe PDF Services request failed.';

        // @phpstan-ignore new.static (subclasses do not override the constructor)
        $exception = new static(sprintf(
            '[%s] %s',
            $code ?? $response->status(),
            $message
        ));

        $exception->errorCode = $code !== null ? (string) $code : null;
        $exception->status = $response->status();
        $exception->context = is_array($body) ? $body : [];

        return $exception;
    }
}
