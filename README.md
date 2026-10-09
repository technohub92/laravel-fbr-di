# Official Laravel FBR Digital Invoicing (DI) SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/technohub92/laravel-fbr-di.svg?style=flat-square)](https://packagist.org/packages/technohub92/laravel-fbr-di)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Fluent, production-ready Laravel SDK for integrating Pakistani FBR PRAL Digital Invoicing (SRO 350/2024 & SRO 1832/2024) directly into any Laravel application, ERP, billing platform, or e-commerce shop.

---

## Key Features
- **Auto-Discovery**: Installs in seconds with Laravel 10, 11, 12, and 13+.
- **Fluent Invoice Builder**: Intuitive, chained API (`FbrDI::invoice()`) for issuing PRAL certified invoices.
- **Interactive CLI Setup**: `php artisan fbr:setup` connects to the gateway and checks credentials interactively.
- **Active Taxpayer Lookup**: `FbrDI::verifyTaxpayer('3520248796577')` checks ATL compliance on the fly.
- **Zero-Contamination Sanitizer**: Automatically cleanses payloads to guarantee 100% PRAL v1.12 specification compliance.
- **Automatic Sandbox / Production Toggle**: Switch modes seamlessly via `.env`.

---

## Installation

```bash
composer require technohub92/laravel-fbr-di
```

### Interactive Setup
Run the setup command to configure your API key and verify connection:
```bash
php artisan fbr:setup
```

Or publish configuration manually:
```bash
php artisan vendor:publish --tag=fbr-di-config
```

---

## 🛠️ Configuration (`.env`)

```env
FBR_DI_API_KEY=fbr_live_xxxxxxxxxxxxxxxxxxxxxxxx
FBR_DI_BASE_URL=https://invoicehub.pk/api
FBR_DI_ENVIRONMENT=sandbox # or 'production'
```

---

## 💻 Usage Examples

### 1. Issue an FBR Certified Invoice
```php
use FbrDI\Facades\FbrDI;
use FbrDI\Builders\InvoiceItemBuilder;

$response = FbrDI::invoice()
    ->invoiceType('Sale Invoice')
    ->scenario('SN001')
    ->invoiceRefNo('INV-2026-0098')
    ->invoiceDate(now())
    ->buyer(fn ($buyer) => $buyer
        ->ntnOrCnic('4210112345671')
        ->businessName('Al-Madina Industrial Traders')
        ->province('Sindh')
        ->address('Plot 14, S.I.T.E Area, Karachi')
        ->registered()
    )
    ->addItem(fn (InvoiceItemBuilder $item) => $item
        ->hsCode('8471.3010')
        ->description('Enterprise Server Terminal')
        ->quantity(2)
        ->uom('Numbers')
        ->unitPrice(120000)
        ->taxRate(18.00)
    )
    ->submit();

if ($response->isSuccessful()) {
    $fbrInvoiceNumber = $response->fbrInvoiceNumber();
    $qrSvg = $response->qrSvg();
    $qrUrl = $response->qrUrl();
} else {
    logger()->error('FBR DI Error:', $response->errors());
}
```

### 2. Verify Taxpayer Active Status (ATL)
```php
$taxpayer = FbrDI::verifyTaxpayer('3520248796577');

if ($taxpayer->isSuccessful()) {
    $data = $taxpayer->raw();
    // ['is_active' => true, 'business_name' => '...', 'category' => 'Corporate']
}
```

### 3. Artisan Commands
```bash
# Test gateway connection and check for SDK updates
php artisan fbr:test-connection

# Check ATL Active status from terminal
php artisan fbr:verify-taxpayer 3520248796577
```

---

## 📄 License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
