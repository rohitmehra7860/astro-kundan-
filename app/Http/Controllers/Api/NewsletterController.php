<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email to the newsletter.
     * Public, rate-limited. Idempotent — re-subscribes if previously unsubscribed.
     */
    public function subscribe(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'name'  => 'nullable|string|max:100',
            ]);

            $subscriber = NewsletterSubscriber::where('email', $validated['email'])->first();

            if ($subscriber) {
                if ($subscriber->status === 'active') {
                    return response()->json([
                        'message' => 'You are already subscribed.',
                    ], 200);
                }

                // Re-activate if previously unsubscribed
                $subscriber->update([
                    'status'        => 'active',
                    'name'          => $validated['name'] ?? $subscriber->name,
                    'subscribed_at' => now(),
                    'token'         => Str::random(64),
                ]);

                return response()->json([
                    'message' => 'You have been re-subscribed successfully!',
                ], 200);
            }

            NewsletterSubscriber::create([
                'email'         => $validated['email'],
                'name'          => $validated['name'] ?? null,
                'status'        => 'active',
                'token'         => Str::random(64),
                'subscribed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Thank you for subscribing!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in NewsletterController@subscribe: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Could not process subscription.'], 500);
        }
    }

    /**
     * Unsubscribe via token (used in email unsubscribe links).
     */
    public function unsubscribe(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string|size:64',
            ]);

            $subscriber = NewsletterSubscriber::where('token', $validated['token'])->first();

            if (!$subscriber) {
                return response()->json(['message' => 'Invalid unsubscribe token.'], 404);
            }

            if ($subscriber->status === 'unsubscribed') {
                return response()->json(['message' => 'You are already unsubscribed.'], 200);
            }

            $subscriber->update(['status' => 'unsubscribed']);

            return response()->json([
                'message' => 'You have been unsubscribed successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in NewsletterController@unsubscribe: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Could not process unsubscription.'], 500);
        }
    }
}
