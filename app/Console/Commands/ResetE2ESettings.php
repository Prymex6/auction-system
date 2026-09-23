<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Console\Command;

class ResetE2ESettings extends Command
{
    protected $signature = 'e2e:reset-settings';

    protected $description = 'Napraw znane, przeciekajace ustawienia platformy pozostawione przez testy E2E';

    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->error('Ta komenda jest zablokowana na produkcji - tylko do testów E2E lokalnie/CI.');

            return;
        }

        PlatformSetting::updateOrCreate([], [
            'enable_user_reports' => true,
            'auto_ban_reports_threshold' => 5,
            'auto_ban_duration_hours' => 24,
            // dostawac 403 TWO_FACTOR_REQUIRED.
            'require_2fa' => false,
        ]);

        $unbanned = User::where('is_banned', true)
            ->where('ban_reason', 'like', 'Automatyczna blokada:%')
            ->where(function ($q) {
                $q->where('name', 'like', 'e2eseller%')
                    ->orWhere('name', 'like', 'e2ereportseller%')
                    ->orWhere('name', 'e2e_seller');
            })
            ->update(['is_banned' => false, 'ban_reason' => null, 'ban_until' => null]);

        $deletedAuctions = Auction::where('title', 'like', 'E2E %')->delete();

        $this->info("E2E settings reset to safe defaults. Auto-unbanned {$unbanned} throwaway/e2e_seller accounts, deleted {$deletedAuctions} orphaned test auctions.");
    }
}
