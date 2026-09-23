<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupUnusedData extends Command
{
    protected $signature = 'cleanup:unused-data';

    protected $description = 'Clean up expired sessions, inactive devices, and old notifications';

    public function handle(): void
    {
        $this->info('Starting data cleanup...');

        // Remove old sessions
        $sessionCount = DB::table('sessions')
            ->where('last_activity', '<', now()->subDays(30)->timestamp)
            ->delete();
        $this->info("Deleted $sessionCount old sessions");

        // Archive old notifications
        $notificationCount = Notification::where('created_at', '<', now()->subDays(30))
            ->delete();
        $this->info("Deleted $notificationCount old notifications");

        $this->info('Data cleanup completed successfully');
    }
}
