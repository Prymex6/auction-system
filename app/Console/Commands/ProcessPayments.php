<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAutoPayments;
use Illuminate\Console\Command;

class ProcessPayments extends Command
{
    protected $signature = 'payments:process';

    protected $description = 'Process automatic payments for auction winners';

    public function handle(): void
    {
        $this->info('Processing automatic payments...');

        dispatch(new ProcessAutoPayments);

        $this->info('Payment processing job dispatched successfully');
    }
}
