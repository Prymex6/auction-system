<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

/**
 * osobne od App\Jobs\EnforceDataRetention (ten job dotyczy RODO/danych
 * przepisami o ochronie danych).
 */
class PruneOperationalLogs implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Nieudane joby - 30 dni wystarcza na zdiagnozowanie problemu,
        DB::table('failed_jobs')->where('failed_at', '<', now()->subDays(30))->delete();

        ErrorLog::where('resolved', true)
            ->where('last_seen_at', '<', now()->subDays(90))
            ->delete();
    }
}
