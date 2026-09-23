<?php

use App\Jobs\CloseExpiredAuctions;
use App\Jobs\EnforceDataRetention;
use App\Jobs\PruneOperationalLogs;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CloseExpiredAuctions)->everyMinute();

Schedule::job(new EnforceDataRetention)->daily();

// App\Jobs\PruneOperationalLogs.
Schedule::job(new PruneOperationalLogs)->daily();
