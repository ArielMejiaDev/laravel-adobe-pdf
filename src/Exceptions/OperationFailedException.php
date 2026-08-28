<?php

namespace ArielMejiaDev\LaravelAdobePdf\Exceptions;

class OperationFailedException extends AdobePdfException
{
    /**
     * Build an exception from a failed Adobe operation status payload.
     *
     * @param  array<string, mixed>  $status
     */
    public static function fromStatus(array $status): self
    {
        $error = $status['error'] ?? $status;

        $code = $error['code'] ?? null;
        $message = $error['message'] ?? 'The Adobe PDF operation failed.';

        $exception = new self(sprintf('[%s] %s', $code ?? 'failed', $message));
        $exception->errorCode = $code !== null ? (string) $code : null;
        $exception->context = $status;

        return $exception;
    }
}
