<?php

namespace FbrDI\Builders;

class BuyerBuilder
{
    protected ?string $ntnCnic = null;
    protected ?string $businessName = null;
    protected ?string $province = null;
    protected ?string $address = null;
    protected string $registrationType = 'Unregistered';

    public function ntnOrCnic(string $ntnCnic): self
    {
        $this->ntnCnic = preg_replace('/[^0-9]/', '', $ntnCnic);
        return $this;
    }

    public function ntn(string $ntn): self
    {
        return $this->ntnOrCnic($ntn);
    }

    public function cnic(string $cnic): self
    {
        return $this->ntnOrCnic($cnic);
    }

    public function businessName(string $businessName): self
    {
        $this->businessName = trim($businessName);
        return $this;
    }

    public function province(string $province): self
    {
        $this->province = trim($province);
        return $this;
    }

    public function address(string $address): self
    {
        $this->address = trim($address);
        return $this;
    }

    public function registrationType(string $type): self
    {
        $this->registrationType = in_array(strtolower($type), ['registered', 'reg']) ? 'Registered' : 'Unregistered';
        return $this;
    }

    public function registered(bool $isRegistered = true): self
    {
        $this->registrationType = $isRegistered ? 'Registered' : 'Unregistered';
        return $this;
    }

    public function toArray(): array
    {
        return [
            'buyerNTNCNIC' => $this->ntnCnic ?? '',
            'buyerBusinessName' => $this->businessName ?? 'Consumer',
            'buyerProvince' => $this->province ?? 'Punjab',
            'buyerAddress' => $this->address ?? 'Pakistan',
            'buyerRegistrationType' => $this->registrationType,
        ];
    }
}
