<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            
            // Only write to DB if last_seen was more than 2 minutes ago
            if (! $user->last_seen || $user->last_seen->diffInMinutes(now()) >= 2) {
                $user->updateQuietly([
                    'last_seen' => now(),
                ]);
            }
        }

        return $next($request);
    }

}
