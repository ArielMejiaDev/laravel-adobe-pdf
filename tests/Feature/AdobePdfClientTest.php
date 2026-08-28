<?php

use ArielMejiaDev\LaravelAdobePdf\Client\AdobePdfClient;
use ArielMejiaDev\LaravelAdobePdf\Client\TokenManager;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\AdobePdfException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\AuthenticationException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Http;

it('fetches and caches the access token', function () {
    Http::fake(['*/token' => Http::response(['access_token' => 'abc', 'expires_in' => 3600])]);

    $manager = app(TokenManager::class);

    expect($manager->token())->toBe('abc')
        ->and($manager->token())->toBe('abc');

    Http::assertSentCount(1);
});

it('throws when credentials are missing', function () {
    config()->set('adobe-pdf.client_id', null);

    expect(fn () => app(TokenManager::class)->token())
        ->toThrow(AuthenticationException::class);
});

it('registers an asset and sends the api key and bearer token', function () {
    fakeAdobeFlow();

    $asset = app(AdobePdfClient::class)->createAsset('application/pdf');

    expect($asset->assetID)->toBe('urn:aaid:asset:1')
        ->and($asset->uploadUri)->toBe('https://blob.test/upload/1');

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/assets')
        && $request->hasHeader('x-api-key', 'test-client-id')
        && $request->hasHeader('Authorization', 'Bearer test-token'));
});

it('returns the polling location from the Location header on submit', function () {
    fakeAdobeFlow();

    $location = app(AdobePdfClient::class)->submit('extractpdf', ['assetID' => 'urn:1']);

    expect($location)->toBe('https://pdf-services.adobe.io/status/job-1');
});

it('raises a rate limit exception on 429', function () {
    Http::fake([
        '*/token' => Http::response(['access_token' => 't', 'expires_in' => 3600]),
        '*/operation/*' => Http::response(['error' => ['code' => 'TOO_MANY_REQUESTS', 'message' => 'Slow down']], 429, ['Retry-After' => '12']),
    ]);

    try {
        app(AdobePdfClient::class)->submit('extractpdf', []);
        $this->fail('Expected a RateLimitException.');
    } catch (RateLimitException $exception) {
        expect($exception->status)->toBe(429)
            ->and($exception->errorCode)->toBe('TOO_MANY_REQUESTS')
            ->and($exception->retryAfter)->toBe(12);
    }
});

it('raises a generic exception carrying the adobe error payload on 400', function () {
    Http::fake([
        '*/token' => Http::response(['access_token' => 't', 'expires_in' => 3600]),
        '*/operation/*' => Http::response(['error' => ['code' => 'INVALID_ASSET_ID', 'message' => 'Bad asset']], 400),
    ]);

    try {
        app(AdobePdfClient::class)->submit('extractpdf', []);
        $this->fail('Expected an AdobePdfException.');
    } catch (AdobePdfException $exception) {
        expect($exception->errorCode)->toBe('INVALID_ASSET_ID')
            ->and($exception->status)->toBe(400)
            ->and($exception->getMessage())->toContain('Bad asset');
    }
});

it('raises an authentication exception on 401', function () {
    Http::fake([
        '*/token' => Http::response(['access_token' => 't', 'expires_in' => 3600]),
        '*/assets' => Http::response(['error' => ['message' => 'Unauthorized']], 401),
    ]);

    expect(fn () => app(AdobePdfClient::class)->createAsset())
        ->toThrow(AuthenticationException::class);
});
