<?php

namespace FbrDI\Commands;

use Illuminate\Console\Command;
use FbrDI\Facades\FbrDI;

class VerifyTaxpayerCommand extends Command
{
    protected $signature = 'fbr:verify-taxpayer {ntn : 7-8 digit NTN or 13-digit Pakistani CNIC}';
    protected $description = 'Verify active taxpayer status (ATL) and business category for an NTN or CNIC';

    public function handle(): int
    {
        $ntn = preg_replace('/[^0-9]/', '', $this->argument('ntn'));
        if (empty($ntn)) {
            $this->error('Please provide a valid NTN or CNIC.');
            return 1;
        }

        $this->info("Querying FBR Active Taxpayer List (ATL) for: {$ntn}...");
        $res = FbrDI::verifyTaxpayer($ntn);

        if ($res->isSuccessful()) {
            $data = $res->raw();
            $this->info('✓ Taxpayer Found:');
            $this->table(['Field', 'Value'], [
                ['NTN / CNIC', $data['ntn_cnic'] ?? $ntn],
                ['Business / Taxpayer Name', $data['taxpayer_name'] ?? ($data['business_name'] ?? 'Verified')],
                ['ATL Status', ($data['is_active'] ?? true) ? 'ACTIVE' : 'INACTIVE'],
                ['Registration Category', $data['category'] ?? 'General'],
            ]);
            return 0;
        }

        $this->warn('Taxpayer not found or inactive: ' . $res->message());
        return 1;
    }
}
