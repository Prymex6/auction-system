<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanInvalidSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean invalid sessions (tokens for users that no longer exist)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning invalid sessions...');

        $tokens = PersonalAccessToken::all();
        $deletedCount = 0;

        foreach ($tokens as $token) {
            if (! $token->tokenable) {
                $token->delete();
                $deletedCount++;
            }
        }

        $this->info("Cleaned {$deletedCount} invalid session(s).");

        return Command::SUCCESS;
    }
}
