<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupQrTokens extends Command
{
    protected $signature = 'auth:cleanup-qr-tokens';
    protected $description = 'Nettoie les tokens QR code expirés';

    public function handle()
    {
        $deleted = DB::table('qr_auth_tokens')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("$deleted tokens QR expirés ont été supprimés.");

        return Command::SUCCESS;
    }
}
