<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckPremiumExpiry extends Command
{
    protected $signature = 'premium:check-expiry';

    protected $description = 'Check and deactivate expired premium accounts';

    public function handle(): void
    {
        $this->info('Checking expired premium accounts...');

        $expiredCount = User::where('premium_until', '<', now())
            ->where('is_premium', true)
            ->update(['is_premium' => false]);

        $this->info("Deactivated $expiredCount expired premium accounts");
    }
}
