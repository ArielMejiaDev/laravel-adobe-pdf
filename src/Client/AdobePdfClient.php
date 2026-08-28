<?php

namespace ArielMejiaDev\LaravelAdobePdf\Client;

use ArielMejiaDev\LaravelAdobePdf\Exceptions\AdobePdfException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\AssetUploadException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\AuthenticationException;
use ArielMejiaDev\LaravelAdobePdf\Exceptions\RateLimitException;
use ArielMejiaDev\LaravelAdobePdf\Support\Asset;
use ArielMejiaDev\LaravelAdobePdf\Support\MediaType;
use ArielMejiaDev\LaravelAdobePdf\Support\OperationResult;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin, low-level wrapper over the Adobe PDF Services REST API.
 *
 * It knows nothing about queues or persistence, it just performs the individual
 * HTTP calls: registering assets, uploading bytes, submitting operations,
 * polling for completion and downloading results.
 */
class AdobePdfClient
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config,
        protected TokenManager $tokens,
    ) {}

    /**
     * Register a new asset and return it together with its presigned upload URL.
     */
    public function createAsset(string $mediaType = MediaType::PDF): Asset
    {
        $response = $this->request()->post('/assets', ['mediaType' => $mediaType]);

        $this->throwForStatus($response);

        return Asset::fromResponse($response->json(), $mediaType);
    }

    /**
     * Upload raw bytes to an asset's presigned upload URL.
     */
    public function upload(Asset $asset, string $contents): void
    {
        if ($asset->uploadUri === null) {
            throw new AssetUploadException('The asset has no upload URI.');
        }

        $response = Http::withBody($contents, $asset->mediaType)->put($asset->uploadUri);

        if ($response->failed()) {
            throw AssetUploadException::fromResponse($response);
        }
    }

    /**
     * Convenience: register an asset and upload its bytes in one call.
     */
    public function uploadContents(string $contents, string $mediaType = MediaType::PDF): Asset
    {
        $asset = $this->createAsset($mediaType);

        $this->upload($asset, $contents);

        return $asset;
    }

    /**
     * Submit an operation and return the URL to poll for its status.
     *
     * @param  array<string, mixed>  $payload
     */
    public function submit(string $operation, array $payload): string
    {
        $response = $this->request()->post('/operation/'.$operation, $payload);

        $this->throwForStatus($response);

        $location = $response->header('Location');

        if ($location === '') {
            $location = (string) $response->json('location');
        }

        if ($location === '') {
            throw new AdobePdfException("Adobe did not return a status location for [{$operation}].");
        }

        return $location;
    }

    /**
     * Poll a previously submitted operation for its current status.
     */
    public function poll(string $location): OperationResult
    {
        $response = $this->request()->get($location);

        $this->throwForStatus($response);

        return OperationResult::fromResponse($response->json() ?? []);
    }

    /**
     * Download the bytes behind a presigned download URI.
     */
    public function download(string $downloadUri): string
    {
        $response = Http::get($downloadUri);

        if ($response->failed()) {
            throw AdobePdfException::fromResponse($response);
        }

        return $response->body();
    }

    /**
     * A pre-authenticated request against the Adobe API base URL.
     */
    protected function request(): PendingRequest
    {
        $http = $this->config['http'] ?? [];

        $request = Http::baseUrl($this->baseUrl())
            ->withToken($this->tokens->token())
            ->withHeaders(['x-api-key' => (string) $this->config['client_id']])
            ->timeout((int) ($http['timeout'] ?? 30))
            ->acceptJson();

        if (! empty($http['retry']['times'])) {
            $request->retry(
                (int) $http['retry']['times'],
                (int) ($http['retry']['sleep'] ?? 250),
                throw: false,
            );
        }

        return $request;
    }

    protected function throwForStatus(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw match ($response->status()) {
            401, 403 => AuthenticationException::fromResponse($response),
            429 => RateLimitException::fromResponse($response),
            default => AdobePdfException::fromResponse($response),
        };
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->config['base_url'], '/');
    }
}
