<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterSubscribedMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Publiczny zapis do newslettera (zgoda — art. 6 ust. 1 lit. a RODO).
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = mb_strtolower(trim($validated['email']));

        if (NewsletterSubscriber::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Ten adres jest już zapisany do newslettera',
            ]);
        }

        NewsletterSubscriber::create([
            'email' => $email,
            'ip_address' => $request->ip(),
        ]);

        Mail::to($email)->send(new NewsletterSubscribedMail($email));

        return response()->json([
            'message' => 'Dziękujemy! Zapisaliśmy Cię do newslettera.',
        ], 201);
    }

    public function index()
    {
        return response()->json([
            'data' => NewsletterSubscriber::orderByDesc('created_at')
                ->get(['id', 'email', 'created_at']),
            'total' => NewsletterSubscriber::count(),
        ]);
    }
}
