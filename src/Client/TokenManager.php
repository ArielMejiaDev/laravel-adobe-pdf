<?php

namespace ArielMejiaDev\LaravelAdobePdf\Client;

use ArielMejiaDev\LaravelAdobePdf\Exceptions\AuthenticationException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Http;

/**
 * Fetches and caches the OAuth Server-to-Server access token.
 *
 * Tokens are cached until shortly before they expire, so we only hit Adobe's
 * token endpoint when we actually need a fresh one.
 */
class TokenManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config,
        protected CacheRepository $cache,
    ) {}

    public function token(): string
    {
        if ($cached = $this->cache->get($this->cacheKey())) {
            return $cached;
        }

        [$token, $expiresIn] = $this->requestToken();

        $leeway = (int) ($this->config['token']['leeway'] ?? 60);

        $this->cache->put($this->cacheKey(), $token, max(1, $expiresIn - $leeway));

        return $token;
    }

    public function forget(): void
    {
        $this->cache->forget($this->cacheKey());
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function requestToken(): array
    {
        $clientId = $this->config['client_id'] ?? null;
        $clientSecret = $this->config['client_secret'] ?? null;

        if (empty($clientId) || empty($clientSecret)) {
            throw AuthenticationException::missingCredentials();
        }

        $response = Http::asForm()->post($this->baseUrl().'/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw AuthenticationException::fromResponse($response);
        }

        return [
            (string) $response->json('access_token'),
            (int) $response->json('expires_in', 86400),
        ];
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->config['base_url'], '/');
    }

    protected function cacheKey(): string
    {
        return (string) ($this->config['token']['key'] ?? 'adobe-pdf.access-token');
    }
}
