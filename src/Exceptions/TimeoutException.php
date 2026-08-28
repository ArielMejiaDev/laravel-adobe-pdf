<?php

namespace ArielMejiaDev\LaravelAdobePdf\Exceptions;

class TimeoutException extends AdobePdfException
{
    public static function afterAttempts(int $attempts): self
    {
        return new self(
            "The Adobe PDF operation did not complete after {$attempts} polling attempts."
        );
    }
}
