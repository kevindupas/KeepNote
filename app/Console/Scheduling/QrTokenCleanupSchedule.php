<?php

namespace App\Console\Scheduling;

use Illuminate\Console\Scheduling\Schedule;

class QrTokenCleanupSchedule
{
    /**
     * Définir la planification pour nettoyer les tokens QR expirés.
     */
    public function __invoke(Schedule $schedule): void
    {
        // Nettoyer les tokens QR expirés toutes les 5 minutes
        $schedule->command('auth:cleanup-qr-tokens')->everyFiveMinutes();
    }
}
