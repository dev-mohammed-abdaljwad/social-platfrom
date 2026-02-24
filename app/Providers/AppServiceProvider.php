<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        \Illuminate\Support\Facades\Blade::directive('mentions', function ($expression) {
            return "<?php echo app(\App\Services\Mentions\MentionsService::class)->render(...{$expression}); ?>";
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Auth rate limiter: 5 attempts per minute per IP (login & register)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many attempts. Please try again later.',
                    ], 429);
                });
        });

        // Friend request rate limiter: 20 attempts per minute per user
        RateLimiter::for('friend-request', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many friend requests. Please slow down.',
                    ], 429);
                });
        });

        // General API rate limiter: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
