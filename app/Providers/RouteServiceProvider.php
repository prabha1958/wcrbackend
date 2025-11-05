<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In Laravel 8+ it's common to leave this null and use FQCN in routes.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {

        $this->configureRateLimiting(
            RateLimiter::for('otp', function (Request $request) {
                $contact = (string) $request->input('contact');

                if ($contact !== '') {
                    // limit by IP + email
                    $key = sha1($request->ip() . '|' . strtolower($contact));
                    return Limit::perHour(5)->by($key);
                }

                // fallback if no contact provided
                return Limit::perMinute(5)->by($request->ip());
            })
        );

        RateLimiter::for('otp', function (Request $request) {
            $contact = (string) $request->input('contact');

            if ($contact !== '') {
                // limit by IP + email
                $key = sha1($request->ip() . '|' . strtolower($contact));
                return Limit::perHour(5)->by($key);
            }

            // fallback if no contact provided
            return Limit::perMinute(5)->by($request->ip());
        });




        $this->routes(function () {
            // API routes (prefix: api, middleware: api)
            Route::prefix('api')
                ->middleware('api')
                // If you prefer automatic controller namespace prepend, uncomment and set $this->namespace and add ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }



    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
