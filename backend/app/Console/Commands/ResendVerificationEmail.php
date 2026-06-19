<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResendVerificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:resend-verification {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend verification email to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        if ($user->isEmailVerified()) {
            $this->warn("Email {$email} is already verified!");
            return 0;
        }

        // Generate new token
        $token = $user->generateEmailVerificationToken();

        // Generate verification URL
        $verificationUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/auth/verify-email/verify-token?token=' . $token . '&email=' . urlencode($user->email);

        // Send email
        try {
            $data = [
                'user' => $user,
                'verification_url' => $verificationUrl,
                'token' => $token
            ];

            Mail::send('emails.email-verification', $data, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Verify Your Email - BizInvestify');
            });

            $this->info("✅ Verification email sent successfully to {$email}!");
            $this->line("\nVerification URL:");
            $this->line($verificationUrl);

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            $this->line("\nYou can still use this verification URL:");
            $this->line($verificationUrl);

            return 1;
        }
    }
}
