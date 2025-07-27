// ... existing code ...

protected $middlewareAliases = [
    // ... existing middleware ...
    // 'permission' => \App\Http\Middleware\PermissionMiddleware::class, // Removed
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    'rate_limit' => \App\Http\Middleware\RateLimitMiddleware::class,
    'security' => \App\Http\Middleware\SecurityMiddleware::class,
];