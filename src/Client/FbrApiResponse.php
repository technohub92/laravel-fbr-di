<?php

namespace FbrDI\Client;

class FbrApiResponse
{
    protected array $data;
    protected int $status;
    protected bool $successful;
    protected ?string $latestVersion;
    protected bool $updateAvailable;

    public function __construct(array $data, int $status, bool $successful, ?string $latestVersion = null, bool $updateAvailable = false)
    {
        $this->data = $data;
        $this->status = $status;
        $this->successful = $successful;
        $this->latestVersion = $latestVersion;
        $this->updateAvailable = $updateAvailable;
    }

    public function isSuccessful(): bool
    {
        return $this->successful && ($this->data['status'] ?? '') !== 'ERROR';
    }

    public function fbrInvoiceNumber(): ?string
    {
        return $this->data['fbr_invoice_number'] 
            ?? $this->data['invoiceNumber'] 
            ?? $this->data['invoice_number'] 
            ?? ($this->data['data']['invoice_number'] ?? null);
    }

    public function qrSvg(): ?string
    {
        return $this->data['qr_svg'] 
            ?? ($this->data['data']['qr_svg'] ?? null);
    }

    public function qrUrl(): ?string
    {
        return $this->data['qr_url'] 
            ?? ($this->data['data']['qr_url'] ?? null);
    }

    public function status(): string
    {
        return $this->data['status'] 
            ?? ($this->isSuccessful() ? 'CERTIFIED' : 'FAILED');
    }

    public function message(): string
    {
        return $this->data['message'] 
            ?? $this->data['error'] 
            ?? ($this->data['data']['message'] ?? ($this->isSuccessful() ? 'Success' : 'Error'));
    }

    public function errors(): array
    {
        return (array) ($this->data['errors'] ?? $this->data['validation_errors'] ?? []);
    }

    public function raw(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function hasUpdateAvailable(): bool
    {
        return $this->updateAvailable;
    }

    public function getLatestVersion(): ?string
    {
        return $this->latestVersion;
    }
}
