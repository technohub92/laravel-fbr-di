<?php

namespace FbrDI\Commands;

use Illuminate\Console\Command;
use FbrDI\Facades\FbrDI;
use Illuminate\Support\Facades\File;

class SetupCommand extends Command
{
    protected $signature = 'fbr:setup {--key= : FBR Gateway API Key} {--env= : Environment (sandbox or production)}';
    protected $description = 'Interactively configure FBR Digital Invoicing Gateway credentials and verify connection';

    public function handle(): int
    {
        $this->info('');
        $this->line('<fg=white;bg=blue;options=bold>  FBR Digital Invoicing (PRAL DI v1.12) Setup Wizard  </>');
        $this->info('─────────────────────────────────────────────────────────────');

        $apiKey = $this->option('key');
        if (empty($apiKey)) {
            $hasKey = $this->choice('Do you have an FBR DI Gateway API Key?', ['Yes, I have an API Key', 'No, I need to create an account'], 0);
            if ($hasKey === 'No, I need to create an account') {
                $portalUrl = config('fbr-di.base_url') ? str_replace('/api', '', config('fbr-di.base_url')) : 'https://your-portal-domain.com';
                $this->warn("👉 Register for your merchant/developer API key at: {$portalUrl}/register");
            }
            $apiKey = $this->secret('Enter your FBR DI API Key (Bearer Token)');
        }

        if (empty($apiKey)) {
            $this->error('Setup aborted: API Key is required.');
            return 1;
        }

        $environment = $this->option('env') ?: $this->choice('Select target environment:', ['sandbox', 'production'], 0);
        $baseUrl = $this->anticipate('Gateway Base API URL:', [
            'https://your-portal-domain.com/api',
            'http://localhost:8000/api',
        ], config('fbr-di.base_url', 'http://localhost:8000/api'));

        $this->info('');
        $this->line('Testing gateway connection with provided credentials...');

        FbrDI::client()->setApiKey($apiKey)->setEnvironment($environment);
        $response = FbrDI::ping();

        if (!$response->isSuccessful()) {
            $this->error("✗ Gateway Ping Failed: " . $response->message());
            $this->line("Please double-check your API Key and Gateway URL.");
            return 1;
        }

        $this->info('✓ Connection Successful!');

        $profile = FbrDI::profile();
        if ($profile->isSuccessful()) {
            $data = $profile->raw();
            $clientName = $data['client']['business_name'] ?? ($data['client']['name'] ?? 'Active Tenant');
            $ntn = $data['client']['fbr_seller_ntn'] ?? ($data['client']['ntn'] ?? 'Configured');
            $this->info("✓ Linked Organization: <comment>{$clientName}</comment> (NTN: {$ntn})");
        }

        // Update .env file
        $this->writeEnvironmentValues([
            'FBR_DI_API_KEY' => $apiKey,
            'FBR_DI_BASE_URL' => $baseUrl,
            'FBR_DI_ENVIRONMENT' => $environment,
        ]);

        $this->info('✓ Environment variables updated in .env successfully.');
        $this->info('');
        $this->line('<fg=green;options=bold>Setup Complete! You can now use FbrDI::invoice() in your Laravel code.</>');
        $this->info('');

        return 0;
    }

    protected function writeEnvironmentValues(array $values): void
    {
        $envFile = base_path('.env');
        if (!File::exists($envFile)) {
            return;
        }

        $content = File::get($envFile);
        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=(.*)$/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        File::put($envFile, $content);
    }
}
