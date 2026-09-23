<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class GenerateE2EVerificationLink extends Command
{
    protected $signature = 'e2e:verify-link {email}';

    protected $description = 'Wygeneruj podpisany link weryfikacyjny dla usera (tylko do testow E2E)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Ta komenda jest zablokowana na produkcji - tylko do testów E2E lokalnie/CI.');

            return 1;
        }

        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('User not found');

            return 1;
        }

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->line($url);

        return 0;
    }
}
