<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PragmaRX\Google2FA\Google2FA;

class GenerateE2ETotpCode extends Command
{
    protected $signature = 'e2e:totp-code {secret}';

    protected $description = 'Wygeneruj aktualnie wazny kod TOTP dla podanego sekretu (do testow E2E)';

    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->error('Ta komenda jest zablokowana na produkcji - tylko do testów E2E lokalnie/CI.');

            return;
        }

        $google2fa = new Google2FA;
        $this->line($google2fa->getCurrentOtp($this->argument('secret')));
    }
}
