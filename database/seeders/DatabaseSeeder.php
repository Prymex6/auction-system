<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the sample data and the legal pages.
     */
    public function run(): void
    {
        $this->call(SampleAuctionsSeeder::class);
        $this->call(LegalPagesSeeder::class);
    }
}
