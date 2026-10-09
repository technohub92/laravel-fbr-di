<?php

namespace FbrDI\Builders;

use FbrDI\Client\FbrApiClient;
use FbrDI\Client\FbrApiResponse;
use Closure;
use DateTimeInterface;

class InvoiceBuilder
{
    protected FbrApiClient $client;

    protected string $invoiceType = 'Sale Invoice';
    protected string $invoiceDate;
    protected string $invoiceRefNo;
    protected string $scenarioId = 'SN001';

    protected ?string $sellerNTNCNIC = null;
    protected ?string $sellerBusinessName = null;
    protected ?string $sellerProvince = null;
    protected ?string $sellerAddress = null;

    protected BuyerBuilder $buyer;
    /** @var InvoiceItemBuilder[] */
    protected array $items = [];

    public function __construct(FbrApiClient $client)
    {
        $this->client = $client;
        $this->invoiceDate = date('Y-m-d');
        $this->invoiceRefNo = 'INV-' . strtoupper(bin2hex(random_bytes(4)));
        $this->buyer = new BuyerBuilder();

        // Load seller defaults from config
        $this->sellerNTNCNIC = config('fbr-di.seller.ntn_cnic');
        $this->sellerBusinessName = config('fbr-di.seller.business_name');
        $this->sellerProvince = config('fbr-di.seller.province', 'Punjab');
        $this->sellerAddress = config('fbr-di.seller.address');
    }

    public function invoiceType(string $type): self
    {
        $this->invoiceType = $type;
        return $this;
    }

    public function scenario(string $scenarioId): self
    {
        $this->scenarioId = strtoupper($scenarioId);
        return $this;
    }

    public function invoiceRefNo(string $refNo): self
    {
        $this->invoiceRefNo = trim($refNo);
        return $this;
    }

    public function invoiceDate($date): self
    {
        if ($date instanceof DateTimeInterface) {
            $this->invoiceDate = $date->format('Y-m-d');
        } else {
            $this->invoiceDate = (string) $date;
        }
        return $this;
    }

    public function seller(string $ntnCnic, string $businessName, string $province, string $address): self
    {
        $this->sellerNTNCNIC = $ntnCnic;
        $this->sellerBusinessName = $businessName;
        $this->sellerProvince = $province;
        $this->sellerAddress = $address;
        return $this;
    }

    /**
     * Configure buyer details via closure or BuyerBuilder instance.
     */
    public function buyer(Closure|BuyerBuilder $callback): self
    {
        if ($callback instanceof Closure) {
            $callback($this->buyer);
        } else {
            $this->buyer = $callback;
        }
        return $this;
    }

    /**
     * Add line item via closure or InvoiceItemBuilder instance.
     */
    public function addItem(Closure|InvoiceItemBuilder $callback): self
    {
        if ($callback instanceof Closure) {
            $item = new InvoiceItemBuilder();
            $callback($item);
            $this->items[] = $item;
        } else {
            $this->items[] = $callback;
        }
        return $this;
    }

    /**
     * Convert the builder to clean PRAL JSON array.
     */
    public function toPayload(): array
    {
        $buyerData = $this->buyer->toArray();

        $itemsData = array_map(fn(InvoiceItemBuilder $i) => $i->toArray(), $this->items);
        if (empty($itemsData)) {
            // Default placeholder item if none added
            $defaultItem = new InvoiceItemBuilder();
            $itemsData[] = $defaultItem->toArray();
        }

        $payload = [
            'invoiceType' => $this->invoiceType,
            'invoiceDate' => $this->invoiceDate,
            'invoiceRefNo' => $this->invoiceRefNo,
            'scenarioId' => $this->scenarioId,
            'sellerNTNCNIC' => $this->sellerNTNCNIC ?? '',
            'sellerBusinessName' => $this->sellerBusinessName ?? '',
            'sellerProvince' => $this->sellerProvince ?? 'Punjab',
            'sellerAddress' => $this->sellerAddress ?? '',
            'buyerNTNCNIC' => $buyerData['buyerNTNCNIC'],
            'buyerBusinessName' => $buyerData['buyerBusinessName'],
            'buyerProvince' => $buyerData['buyerProvince'],
            'buyerAddress' => $buyerData['buyerAddress'],
            'buyerRegistrationType' => $buyerData['buyerRegistrationType'],
            'items' => $itemsData,
        ];

        return $payload;
    }

    /**
     * Submit invoice to FBR DI Gateway.
     */
    public function submit(bool $sandbox = false): FbrApiResponse
    {
        return $this->client->submitInvoice($this->toPayload(), $sandbox);
    }

    /**
     * Pre-validate invoice against PRAL rules without issuing.
     */
    public function validate(bool $sandbox = false): FbrApiResponse
    {
        return $this->client->validateInvoice($this->toPayload(), $sandbox);
    }
}
