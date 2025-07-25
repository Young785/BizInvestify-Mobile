<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Submit a contact form
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|in:general,support,sales,partnership,press,other',
            'message' => 'required|string|max:2000',
            'consent' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Store contact submission in database (optional)
            $contactData = [
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'submitted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            // Log the contact submission
            Log::info('Contact form submitted', $contactData);

            // Send email notification to admin
            $this->sendContactEmail($contactData);

            // Send confirmation email to user
            $this->sendConfirmationEmail($contactData);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Contact form submission failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error submitting your message. Please try again or contact us directly.',
            ], 500);
        }
    }

    /**
     * Send email notification to admin
     */
    private function sendContactEmail(array $data): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@bizinvestify.com');
            
            Mail::send('emails.contact-notification', $data, function ($message) use ($data, $adminEmail) {
                $message->to($adminEmail)
                        ->subject('New Contact Form Submission - ' . ucfirst($data['subject']))
                        ->replyTo($data['email'], $data['first_name'] . ' ' . $data['last_name']);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send contact email to admin: ' . $e->getMessage());
        }
    }

    /**
     * Send confirmation email to user
     */
    private function sendConfirmationEmail(array $data): void
    {
        try {
            Mail::send('emails.contact-confirmation', $data, function ($message) use ($data) {
                $message->to($data['email'], $data['first_name'] . ' ' . $data['last_name'])
                        ->subject('Thank you for contacting BizInvestify');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email to user: ' . $e->getMessage());
        }
    }

    /**
     * Get contact form statistics (admin only)
     */
    public function getStats(): JsonResponse
    {
        try {
            // This would typically fetch from a database
            // For now, return sample stats
            $stats = [
                'total_submissions' => 0,
                'pending_responses' => 0,
                'response_time_avg' => '2 hours',
                'popular_subjects' => [
                    'general' => 0,
                    'support' => 0,
                    'sales' => 0,
                    'partnership' => 0,
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact statistics'
            ], 500);
        }
    }
} 