<?php

namespace FbrDI\Client;

use FbrDI\Exceptions\FbrApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class FbrApiClient
{
    public const VERSION = '1.0.0';

    protected string $baseUrl;
    protected string $apiKey;
    protected string $environment;
    protected int $timeout;
    protected int $retries;

    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim($config['base_url'] ?? config('fbr-di.base_url', 'http://localhost/api'), '/');
        $this->apiKey = $config['api_key'] ?? config('fbr-di.api_key', '');
        $this->environment = $config['environment'] ?? config('fbr-di.environment', 'sandbox');
        $this->timeout = (int) ($config['timeout'] ?? config('fbr-di.timeout', 30));
        $this->retries = (int) ($config['retries'] ?? config('fbr-di.retries', 3));
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isSandbox(): bool
    {
        return strtolower($this->environment) === 'sandbox';
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retries, 100)
            ->withToken($this->apiKey)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'FbrDigitalInvoicing-LaravelSDK/' . self::VERSION,
                'X-Client-Platform' => 'Laravel/' . (defined('LARAVEL_START') ? 'Modern' : 'Framework'),
            ]);
    }

    /**
     * Submit an invoice to the Gateway.
     */
    public function submitInvoice(array $payload, bool $sandbox = false): FbrApiResponse
    {
        $useSandbox = $sandbox || $this->isSandbox();
        $endpoint = $useSandbox ? '/postinvoicedata_sb' : '/postinvoicedata';

        return $this->post($endpoint, $payload);
    }

    /**
     * Validate an invoice against PRAL rules without issuing.
     */
    public function validateInvoice(array $payload, bool $sandbox = false): FbrApiResponse
    {
        $useSandbox = $sandbox || $this->isSandbox();
        $endpoint = $useSandbox ? '/validateinvoicedata_sb' : '/validateinvoicedata';

        return $this->post($endpoint, $payload);
    }

    /**
     * Check Active Taxpayer List (ATL) status.
     */
    public function verifyTaxpayer(string $ntnOrCnic): FbrApiResponse
    {
        return $this->get('/statl', ['ntn' => $ntnOrCnic]);
    }

    /**
     * Fetch connected client legal profile.
     */
    public function getClientProfile(): FbrApiResponse
    {
        return $this->get('/client-profile');
    }

    /**
     * Ping the API Gateway.
     */
    public function ping(): FbrApiResponse
    {
        return $this->get('/ping');
    }

    /**
     * Fetch standard reference dataset (provinces, uom, doctypes, hs-codes).
     */
    public function getReference(string $type): FbrApiResponse
    {
        return $this->get('/di/reference/' . urlencode($type));
    }

    protected function post(string $endpoint, array $payload): FbrApiResponse
    {
        $response = $this->http()->post($endpoint, $payload);
        return $this->handleResponse($response);
    }

    protected function get(string $endpoint, array $query = []): FbrApiResponse
    {
        $response = $this->http()->get($endpoint, $query);
        return $this->handleResponse($response);
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response): FbrApiResponse
    {
        $latestVersion = $response->header('X-FBR-SDK-Latest-Version');
        $updateAvailable = $response->header('X-FBR-SDK-Update-Available') === 'true'
            || ($latestVersion && version_compare($latestVersion, self::VERSION, '>'));

        $data = $response->json();
        if (!is_array($data)) {
            $data = ['message' => $response->body(), 'status' => $response->successful() ? 'SUCCESS' : 'ERROR'];
        }

        return new FbrApiResponse(
            data: $data,
            status: $response->status(),
            successful: $response->successful(),
            latestVersion: $latestVersion,
            updateAvailable: $updateAvailable
        );
    }
}
