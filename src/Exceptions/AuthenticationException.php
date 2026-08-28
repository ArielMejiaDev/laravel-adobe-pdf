<?php

namespace ArielMejiaDev\LaravelAdobePdf\Exceptions;

class AuthenticationException extends AdobePdfException
{
    public static function missingCredentials(): self
    {
        return new self(
            'Adobe PDF Services credentials are missing. Set ADOBE_PDF_CLIENT_ID and ADOBE_PDF_CLIENT_SECRET.'
        );
    }
}
