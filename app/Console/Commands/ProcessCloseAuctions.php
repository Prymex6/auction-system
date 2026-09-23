<?php

namespace App\Console\Commands;

use App\Jobs\CloseExpiredAuctions;
use Illuminate\Console\Command;

class ProcessCloseAuctions extends Command
{
    protected $signature = 'auctions:close';

    protected $description = 'Close expired auctions and determine winners';

    public function handle(): void
    {
        $this->info('Processing expired auctions...');

        dispatch(new CloseExpiredAuctions);

        $this->info('Auction closure job dispatched successfully');
    }
}
