<?php

namespace Tests\Feature;

use App\Jobs\EnforceDataRetention;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_user_devices_older_than_24_months()
    {
        $user = User::factory()->create();

        $old = UserDevice::create([
            'user_id' => $user->id,
            'device_token' => 'old-device',
            'device_name' => 'Stary telefon',
            'device_type' => 'mobile',
            'ip_address' => '10.0.0.1',
            'last_activity_at' => now()->subMonths(25),
        ]);
        $recent = UserDevice::create([
            'user_id' => $user->id,
            'device_token' => 'recent-device',
            'device_name' => 'Nowy telefon',
            'device_type' => 'mobile',
            'ip_address' => '10.0.0.2',
            'last_activity_at' => now()->subMonths(1),
        ]);

        (new EnforceDataRetention)->handle();

        $this->assertDatabaseMissing('user_devices', ['id' => $old->id]);
        $this->assertDatabaseHas('user_devices', ['id' => $recent->id]);
    }

    public function test_purges_audit_logs_older_than_24_months()
    {
        $old = AuditLog::create([
            'action' => 'stara_akcja',
            'ip_address' => '10.0.0.1',
        ]);
        $old->created_at = now()->subMonths(25);
        $old->save();

        $recent = AuditLog::create([
            'action' => 'nowa_akcja',
            'ip_address' => '10.0.0.2',
        ]);

        (new EnforceDataRetention)->handle();

        $this->assertDatabaseMissing('audit_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recent->id]);
    }
}
