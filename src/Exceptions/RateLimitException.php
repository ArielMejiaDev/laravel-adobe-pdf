<?php

namespace ArielMejiaDev\LaravelAdobePdf\Exceptions;

use Illuminate\Http\Client\Response;

class RateLimitException extends AdobePdfException
{
    /**
     * Seconds to wait before retrying, taken from the Retry-After header.
     */
    public ?int $retryAfter = null;

    public static function fromResponse(Response $response): static
    {
        $exception = parent::fromResponse($response);

        $retryAfter = $response->header('Retry-After');
        $exception->retryAfter = $retryAfter !== '' ? (int) $retryAfter : null;

        return $exception;
    }
}
