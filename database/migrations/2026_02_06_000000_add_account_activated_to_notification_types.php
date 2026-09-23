<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // For MySQL: Modify the ENUM column to add the new value
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'bid_placed', 'outbid', 'auction_won', 'auction_ended',
            'auction_extended', 'new_bid',
            'message_received', 'message_response',
            'listing_approved', 'listing_rejected',
            'system_alert', 'premium_expired',
            'account_activated'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Revert back to original ENUM values
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'bid_placed', 'outbid', 'auction_won', 'auction_ended',
            'auction_extended', 'new_bid',
            'message_received', 'message_response',
            'listing_approved', 'listing_rejected',
            'system_alert', 'premium_expired'
        ) NOT NULL");
    }
};
