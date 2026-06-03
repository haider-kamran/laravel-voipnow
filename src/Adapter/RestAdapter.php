<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Adapter;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use HyderKamran\VoipNow\Contracts\ConnectorInterface;
use HyderKamran\VoipNow\Exception\VoipNowException;
use HyderKamran\VoipNow\Traits\HandlesVoipNowConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * RestAdapter — primary adapter for the VoipNow UnifiedAPI v5 (REST/JSON).
 *
 * Handles OAuth2 token acquisition, automatic token refresh, and all
 * HTTP verb methods required by ConnectorInterface. Token storage is
 * attempted on the authenticated user model; falls back to the Laravel
 * cache (useful for queued jobs or CLI commands).
 */
class RestAdapter implements ConnectorInterface
{
    use HandlesVoipNowConfig;

    protected array $config = [];
    protected ?Guzzle $httpClient = null;

    public function connect(array $config): static
    {
        $this->config = $this->normalizeConfig($config);
        $this->httpClient = $this->createHttpClient();

        return $this;
    }

    protected function createHttpClient(): Guzzle
    {
        $domain = $this->getDomain($this->config);

        if (empty($domain)) {
            throw new VoipNowException('The VOIPNOW_DOMAIN configuration value is required.');
        }

        return new Guzzle([
            'base_uri'    => $domain . '/api/v5/',
            'timeout'     => 60,
            'headers'     => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'http_errors' => false,
        ]);
    }

    // -----------------------------------------------------------------------
    // Token management
    // -----------------------------------------------------------------------

    protected function getToken(): string
    {
        $info = $this->getTokenInfo();

        if (empty($info['voipnow_access_token']) || $this->tokenIsExpired($info)) {
            $info = $this->fetchFreshToken();
            $this->storeTokenInfo($info);
        }

        return (string) $info['voipnow_access_token'];
    }

    protected function tokenIsExpired(array $info): bool
    {
        return empty($info['voipnow_token_expired_at'])
            || $info['voipnow_token_expired_at'] <= now()->toDateTimeString();
    }

    protected function fetchFreshToken(): array
    {
        $this->requireCredentials($this->config);

        try {
            $response = (new Guzzle())->post($this->getTokenEndpoint($this->config), [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->config['voip_key'],
                    'client_secret' => $this->config['voip_secret'],
                ],
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new VoipNowException('Unable to request VoipNow token: ' . $e->getMessage(), $e->getCode(), $e);
        }

        $payload = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($payload['access_token'])) {
            throw new VoipNowException('Could not retrieve a valid VoipNow access token.');
        }

        $expiresIn = (int) ($payload['expires_in'] ?? 3600);

        return [
            'voipnow_access_token'     => $payload['access_token'],
            'voipnow_token_expires_in' => $expiresIn,
            'voipnow_token_expired_at' => now()->addSeconds($expiresIn)->toDateTimeString(),
        ];
    }

    protected function storeTokenInfo(array $info): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->voipnow_access_token     = $info['voipnow_access_token'];
            $user->voipnow_token_expires_in = $info['voipnow_token_expires_in'];
            $user->voipnow_token_expired_at = $info['voipnow_token_expired_at'];
            $user->save();
            return;
        }

        Cache::put('voipnow_token_info', $info, $info['voipnow_token_expires_in']);
    }

    protected function getTokenInfo(): array
    {
        if (Auth::check()) {
            $user = Auth::user();
            return [
                'voipnow_access_token'     => $user->voipnow_access_token ?? null,
                'voipnow_token_expires_in' => $user->voipnow_token_expires_in ?? null,
                'voipnow_token_expired_at' => $user->voipnow_token_expired_at ?? null,
            ];
        }

        return Cache::get('voipnow_token_info', [
            'voipnow_access_token'     => null,
            'voipnow_token_expires_in' => null,
            'voipnow_token_expired_at' => null,
        ]);
    }

    // -----------------------------------------------------------------------
    // HTTP verb methods — ConnectorInterface implementation
    // -----------------------------------------------------------------------

    public function get(string $endpoint, array $queryParams = []): array
    {
        return $this->send('GET', $endpoint, ['query' => $queryParams]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->send('POST', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->send('PUT', $endpoint, ['json' => $data]);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->send('PATCH', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->send('DELETE', $endpoint, ['json' => $data]);
    }

    protected function send(string $method, string $endpoint, array $options = []): array
    {
        if ($this->httpClient === null) {
            throw new VoipNowException('RestAdapter is not connected. Call connect() first.');
        }

        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
        } catch (GuzzleException $e) {
            throw new VoipNowException('VoipNow API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }

        $statusCode = $response->getStatusCode();
        $body       = (string) $response->getBody();
        $payload    = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new VoipNowException('Invalid JSON response from VoipNow API (status ' . $statusCode . ').');
        }

        if ($statusCode >= 400) {
            $message = $payload['message'] ?? $payload['error'] ?? 'Unknown error';
            throw new VoipNowException(
                sprintf('VoipNow API error [%d]: %s', $statusCode, $message),
                $statusCode
            );
        }

        return $payload ?? [];
    }
}
