<?php

namespace FbrDI\Commands;

use Illuminate\Console\Command;
use FbrDI\Facades\FbrDI;

class TestConnectionCommand extends Command
{
    protected $signature = 'fbr:test-connection';
    protected $description = 'Test communication with FBR Digital Invoicing Gateway and inspect API version status';

    public function handle(): int
    {
        $this->info('Testing FBR Digital Invoicing Gateway Connection...');
        $this->table(['Config Key', 'Value'], [
            ['Base URL', config('fbr-di.base_url')],
            ['Environment', config('fbr-di.environment')],
            ['API Key Configured', !empty(config('fbr-di.api_key')) ? 'Yes ('.substr(config('fbr-di.api_key'), 0, 6).'***)' : 'NO (Missing)'],
        ]);

        if (empty(config('fbr-di.api_key'))) {
            $this->error('Error: FBR_DI_API_KEY is not configured in .env. Run `php artisan fbr:setup` to configure.');
            return 1;
        }

        $res = FbrDI::ping();
        if ($res->isSuccessful()) {
            $this->info('✓ Status: ONLINE & AUTHENTICATED');
            $this->line('Gateway Response Time: ' . $res->getStatusCode() . ' OK');

            if ($res->hasUpdateAvailable()) {
                $this->warn("⚠ Notice: An updated version of ebcdisc/fbr-digital-invoicing ({$res->getLatestVersion()}) is available!");
                $this->line("👉 Run 'composer update ebcdisc/fbr-digital-invoicing' to update.");
            }

            return 0;
        }

        $this->error('✗ Connection Failed: ' . $res->message());
        return 1;
    }
}
