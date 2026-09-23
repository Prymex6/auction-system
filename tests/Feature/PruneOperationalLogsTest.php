<?php

namespace Tests\Feature;

use App\Jobs\PruneOperationalLogs;
use App\Models\ErrorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PruneOperationalLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_failed_jobs_older_than_30_days()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'stara',
            'failed_at' => now()->subDays(31),
        ]);
        $recentId = DB::table('failed_jobs')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'nowa',
            'failed_at' => now()->subDays(1),
        ]);

        (new PruneOperationalLogs)->handle();

        $this->assertSame(1, DB::table('failed_jobs')->count());
        $this->assertDatabaseHas('failed_jobs', ['id' => $recentId]);
    }

    public function test_purges_resolved_error_logs_older_than_90_days_but_keeps_unresolved()
    {
        $oldResolved = ErrorLog::create([
            'fingerprint' => hash('sha256', 'a'),
            'exception_class' => 'RuntimeException',
            'message' => 'stary rozwiazany',
            'occurrences' => 1,
            'first_seen_at' => now()->subDays(100),
            'last_seen_at' => now()->subDays(91),
            'resolved' => true,
        ]);
        $oldUnresolved = ErrorLog::create([
            'fingerprint' => hash('sha256', 'b'),
            'exception_class' => 'RuntimeException',
            'message' => 'stary nierozwiazany',
            'occurrences' => 1,
            'first_seen_at' => now()->subDays(100),
            'last_seen_at' => now()->subDays(91),
            'resolved' => false,
        ]);
        $recentResolved = ErrorLog::create([
            'fingerprint' => hash('sha256', 'c'),
            'exception_class' => 'RuntimeException',
            'message' => 'niedawno rozwiazany',
            'occurrences' => 1,
            'first_seen_at' => now()->subDays(10),
            'last_seen_at' => now()->subDays(5),
            'resolved' => true,
        ]);

        (new PruneOperationalLogs)->handle();

        $this->assertDatabaseMissing('error_logs', ['id' => $oldResolved->id]);
        $this->assertDatabaseHas('error_logs', ['id' => $oldUnresolved->id]);
        $this->assertDatabaseHas('error_logs', ['id' => $recentResolved->id]);
    }
}
