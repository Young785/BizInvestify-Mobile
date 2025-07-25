<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Security checks
        $this->validateRequestSecurity($request);
        
        // Sanitize input data
        $this->sanitizeInput($request);
        
        $response = $next($request);
        
        // Add security headers
        $this->addSecurityHeaders($response);
        
        return $response;
    }

    /**
     * Validate request security.
     */
    protected function validateRequestSecurity(Request $request): void
    {
        // Check for suspicious patterns
        $suspiciousPatterns = [
            // SQL Injection patterns
            '/(\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bunion\b|\bdrop\b|\bcreate\b|\balter\b)/i',
            // XSS patterns
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            // Path traversal
            '/\.\.\//i',  
            '/\.\.[\/\\\\]/i',
            // Command injection
            '/(\bexec\b|\bsystem\b|\bshell_exec\b|\bpassthru\b)/i',
        ];

        $allInput = json_encode($request->all());
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $allInput)) {
                Log::warning('Suspicious request detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'pattern_matched' => $pattern,
                    'input' => substr($allInput, 0, 500)
                ]);
                
                abort(400, 'Bad Request');
            }
        }

        // Check request size to prevent DoS
        if (strlen($allInput) > 100000) { // 100KB limit
            Log::warning('Large request blocked', [
                'ip' => $request->ip(),
                'size' => strlen($allInput),
                'path' => $request->path()
            ]);
            
            abort(413, 'Request Entity Too Large');
        }

        // Validate User-Agent to block obvious bots
        $userAgent = $request->userAgent();
        $blockedAgents = [
            'sqlmap',
            'nikto',
            'nessus',
            'openvas',
            'burpsuite',
            'havij',
            'w3af'
        ];

        foreach ($blockedAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                Log::warning('Blocked user agent detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $userAgent,
                    'blocked_agent' => $agent
                ]);
                
                abort(403, 'Forbidden');
            }
        }
    }

    /**
     * Sanitize input data.
     */
    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes
                $value = str_replace("\0", '', $value);
                
                // Trim whitespace
                $value = trim($value);
                
                // Replace the sanitized value
                $request->merge([$key => $value]);
            }
        }
    }

    /**
     * Add security headers to response.
     */
    protected function addSecurityHeaders(Response $response): void
    {
        $securityHeaders = [
            // Prevent clickjacking
            'X-Frame-Options' => 'DENY',
            
            // XSS Protection
            'X-XSS-Protection' => '1; mode=block',
            
            // Content type sniffing protection
            'X-Content-Type-Options' => 'nosniff',
            
            // Referrer policy
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Content Security Policy
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https:; frame-src 'none';",
            
            // Strict Transport Security (if HTTPS)
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            
            // Permissions policy
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            
            // Remove server information
            'Server' => 'BizInvestify'
        ];

        foreach ($securityHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }
    }

    /**
     * Validate email format with additional security checks.
     */
    public static function validateSecureEmail(string $email): bool
    {
        // Basic email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check for suspicious patterns in email
        $suspiciousPatterns = [
            '/\+.*@/',  // Plus addressing might be used for spam
            '/\.{2,}/',  // Multiple consecutive dots
            '/[<>]/',    // Angle brackets
            '/javascript:/i',  // JavaScript protocol
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $email)) {
                return false;
            }
        }

        // Check domain length
        $domain = substr(strrchr($email, '@'), 1);
        if (strlen($domain) > 255) {
            return false;
        }

        return true;
    }

    /**
     * Validate password strength.
     */
    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];
        $strength = 0;

        // Length check
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $strength += 1;
        }

        // Uppercase check
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $strength += 1;
        }

        // Lowercase check
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $strength += 1;
        }

        // Number check
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $strength += 1;
        }

        // Special character check
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        } else {
            $strength += 1;
        }

        // Common password check
        $commonPasswords = [
            'password', '123456', 'password123', 'admin', 'letmein',
            'welcome', 'monkey', '1234567890', 'qwerty', 'abc123'
        ];

        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'Password is too common';
            $strength = 0;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $strength
        ];
    }
}