<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\UserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Railway load balancer sends HTTP internally)
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
        $middleware->encryptCookies(except: [
            'user_login_appearance',
        ]);
        $middleware->web(append: [
            UserActivity::class,
        ]);

        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function ($schedule): void {
        // Auto-delete old notifications when they reach their expiration date
        $schedule->command('notifications:cleanup')->hourly();

        // Keep the SQL backup file in sync with the database
        $schedule->command('db:backup')->everyFiveMinutes();
    })
    ->create();
