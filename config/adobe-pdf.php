<?php

// config for ArielMejiaDev/LaravelAdobePdf
return [

    /*
    |--------------------------------------------------------------------------
    | Adobe PDF Services credentials
    |--------------------------------------------------------------------------
    |
    | These come from your Adobe Developer Console project using the
    | "OAuth Server-to-Server" credential. The client id is also sent as the
    | "x-api-key" header on every API call.
    |
    */

    'base_url' => env('ADOBE_PDF_BASE_URL', 'https://pdf-services.adobe.io'),

    'client_id' => env('ADOBE_PDF_CLIENT_ID'),

    'client_secret' => env('ADOBE_PDF_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Access token caching
    |--------------------------------------------------------------------------
    |
    | Access tokens are short lived. They are cached until shortly before they
    | expire ("leeway" seconds early) so we do not fetch a new one on every
    | request. Set "store" to null to use the default cache store.
    |
    */

    'token' => [
        'store' => env('ADOBE_PDF_TOKEN_CACHE_STORE'),
        'key' => 'adobe-pdf.access-token',
        'leeway' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue configuration
    |--------------------------------------------------------------------------
    |
    | Every operation is processed through a queued job by default. These
    | settings decide which connection and queue those jobs are pushed onto.
    |
    */

    'queue' => [
        'connection' => env('ADOBE_PDF_QUEUE_CONNECTION'),
        'queue' => env('ADOBE_PDF_QUEUE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Job polling
    |--------------------------------------------------------------------------
    |
    | Adobe operations are asynchronous: we submit a job and then poll it until
    | it is "done". Instead of blocking a worker with sleep(), the job releases
    | itself back onto the queue every "backoff" seconds and gives up after
    | "max_attempts" polls.
    |
    */

    'polling' => [
        'backoff' => 5,
        'max_attempts' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => 30,
        'retry' => [
            'times' => 3,
            'sleep' => 250,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | "disk" and "path" decide where source files are staged before upload and
    | where the resulting documents are stored once an operation completes.
    |
    */

    'storage' => [
        'disk' => env('ADOBE_PDF_DISK', 'local'),
        'path' => env('ADOBE_PDF_PATH', 'adobe-pdf'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Process tracking
    |--------------------------------------------------------------------------
    |
    | When enabled, every operation is recorded in the database so its progress,
    | result and any error can be inspected later (and surfaced in a dashboard).
    |
    */

    'tracking' => [
        'enabled' => true,
        'table' => 'adobe_pdf_processes',
    ],

];
