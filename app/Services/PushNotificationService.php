<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * Web Push notifications of our own, to the standard, with no Firebase and no
 * Apple developer account: they work as a PWA on Android and on iOS 16.4+
 * once the page is added to the home screen. The VAPID keys identify this
 * server as an authorised sender; see config/services.php.
 *
 * Nothing here is allowed to fail loudly. A notification is a side effect of
 * registering, writing a message or reporting something, and the person doing
 * that must not see an error because a push endpoint, or our own VAPID
 * configuration, happens to be broken.
 */
class PushNotificationService
{
    /**
     * Send to every subscription an account has — there may be several, a
     * phone and a computer. Subscriptions the push service rejects as gone
     * (410) or unknown (404) are deleted, because they will never work again.
     */
    public function sendToUser(User $user, string $title, string $body, ?string $url = null): void
    {
        $subscriptions = $user->pushSubscriptions;
        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/',
        ]);

        // The whole send sits inside the guard, not just the delivery:
        // building the client validates the VAPID keys and throws when they
        // are missing or malformed, which is exactly the case that used to
        // turn a missed notification into a failed registration.
        try {
            $webPush = $this->webPush();

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding ?? 'aesgcm',
                    ]),
                    $payload
                );
            }

            $this->deliver($webPush);
        } catch (Throwable $error) {
            Log::warning('Push notification not sent', [
                'user_id' => $user->id,
                'error' => $error->getMessage(),
            ]);
        }
    }

    /**
     * Send to every administrator: a new registration, a new report, an
     * application error — things the people running the platform care about
     * rather than any one user.
     */
    public function sendToAdmins(string $title, string $body, ?string $url = null): void
    {
        try {
            User::where('is_admin', true)
                ->with('pushSubscriptions')
                ->get()
                ->each(fn (User $admin) => $this->sendToUser($admin, $title, $body, $url));
        } catch (Throwable $error) {
            // Even reading the administrators must not break the caller.
            Log::warning('Push notification to administrators not sent', [
                'error' => $error->getMessage(),
            ]);
        }
    }

    private function webPush(): WebPush
    {
        return new WebPush([
            'VAPID' => [
                'subject' => config('services.vapid.subject'),
                'publicKey' => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Push everything queued and forget the subscriptions that are gone.
     */
    private function deliver(WebPush $webPush): void
    {
        foreach ($webPush->flush() as $report) {
            $status = $report->getResponse()?->getStatusCode();
            if (! $report->isSuccess() && in_array($status, [404, 410], true)) {
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
            }
        }
    }
}
