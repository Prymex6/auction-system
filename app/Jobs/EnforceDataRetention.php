<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\UserDevice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Egzekwuje okres przechowywania "logow technicznych" zadeklarowany w
 * Polityce Prywatnosci (pkt 5.3: maksymalnie 24 miesiace) - urzadzenia/
 *
 * (ProfileController::destroy) mimo ze polityka wspomina "12 miesiecy po
 */
class EnforceDataRetention implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        UserDevice::where('last_activity_at', '<', now()->subMonths(24))->delete();
        AuditLog::where('created_at', '<', now()->subMonths(24))->delete();
    }
}
