<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email to the newsletter (public).
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'source' => 'sometimes|string|max:50',
            'locale' => 'sometimes|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $email = strtolower(trim($request->email));
            $existing = NewsletterSubscriber::where('email', $email)->first();

            if ($existing) {
                if ($existing->unsubscribed_at) {
                    $existing->update([
                        'unsubscribed_at' => null,
                        'subscribed_at' => now(),
                        'source' => $request->input('source', 'footer'),
                        'locale' => $request->input('locale'),
                        'ip_address' => $request->ip(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Welcome back! You have been re-subscribed to our newsletter.',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'You are already subscribed to our newsletter.',
                ]);
            }

            NewsletterSubscriber::create([
                'email' => $email,
                'source' => $request->input('source', 'footer'),
                'locale' => $request->input('locale'),
                'ip_address' => $request->ip(),
                'subscribed_at' => now(),
            ]);

            Log::info('Newsletter subscription', ['email' => $email, 'source' => $request->input('source', 'footer')]);

            return response()->json([
                'success' => true,
                'message' => 'Thanks for subscribing! You will receive our latest updates.',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Newsletter subscribe failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to subscribe right now. Please try again later.',
            ], 500);
        }
    }

    /**
     * Unsubscribe from the newsletter (public).
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $email = strtolower(trim($request->email));
            $subscriber = NewsletterSubscriber::where('email', $email)->first();

            if (!$subscriber || $subscriber->unsubscribed_at) {
                return response()->json([
                    'success' => true,
                    'message' => 'This email is not on our newsletter list.',
                ]);
            }

            $subscriber->update(['unsubscribed_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'You have been unsubscribed from our newsletter.',
            ]);
        } catch (\Exception $e) {
            Log::error('Newsletter unsubscribe failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to unsubscribe right now. Please try again later.',
            ], 500);
        }
    }
}
