<?php

namespace FbrDI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \FbrDI\Builders\InvoiceBuilder invoice()
 * @method static \FbrDI\Client\FbrApiResponse verifyTaxpayer(string $ntnOrCnic)
 * @method static \FbrDI\Client\FbrApiResponse profile()
 * @method static \FbrDI\Client\FbrApiResponse ping()
 * @method static \FbrDI\Client\FbrApiClient client()
 * 
 * @see \FbrDI\FbrDiManager
 */
class FbrDI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fbr-di';
    }
}
