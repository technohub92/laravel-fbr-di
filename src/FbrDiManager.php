<?php

namespace FbrDI;

use FbrDI\Client\FbrApiClient;
use FbrDI\Client\FbrApiResponse;
use FbrDI\Builders\InvoiceBuilder;

class FbrDiManager
{
    protected FbrApiClient $client;

    public function __construct(FbrApiClient $client)
    {
        $this->client = $client;
    }

    public function client(): FbrApiClient
    {
        return $this->client;
    }

    /**
     * Start a new fluent Invoice Builder.
     */
    public function invoice(): InvoiceBuilder
    {
        return new InvoiceBuilder($this->client);
    }

    /**
     * Verify Active Taxpayer List (ATL) status.
     */
    public function verifyTaxpayer(string $ntnOrCnic): FbrApiResponse
    {
        return $this->client->verifyTaxpayer($ntnOrCnic);
    }

    /**
     * Fetch connected client profile from gateway.
     */
    public function profile(): FbrApiResponse
    {
        return $this->client->getClientProfile();
    }

    /**
     * Test connection to the gateway.
     */
    public function ping(): FbrApiResponse
    {
        return $this->client->ping();
    }
}
