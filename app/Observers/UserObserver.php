<?php

namespace App\Observers;

use App\Mail\AccountBannedMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function created(User $user): void
    {
        // Send welcome email
        Mail::queue(new WelcomeMail($user));

        if (! $user->hasVerifiedEmail()) {
            Mail::queue(new VerifyEmailMail($user));
        }
    }

    public function updated(User $user): void
    {
        if ($user->isDirty('is_banned') && $user->is_banned) {
            $duration = $user->ban_until
                ? $user->ban_until->diffInDays(now()).' dni'
                : 'na zawsze';

            Mail::queue(new AccountBannedMail(
                $user,
                $user->ban_reason ?? 'Brak podanego powodu',
                $duration
            ));
        }
    }
}
