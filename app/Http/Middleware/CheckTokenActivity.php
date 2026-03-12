<?php

namespace App\Http\Middleware;

use App\Responser\JsonResponser;
use Closure;
use Illuminate\Http\Request;

/**
 * NOTE ON ROUTE BINDING & INACTIVITY TRACKING
 *
 * There is a subtle challenge with this middleware when route-model binding fails.
 * In such cases, the request never reaches this middleware, which means:
 * - `last_activity_at` is not updated for that request.
 * - From the user's perspective they are "active" (hitting endpoints),
 *   but the system still sees them as inactive.
 * - This can eventually cause unintended lockouts due to inactivity.
 *
 * Possible solutions that were tested:
 *  1. Prepending this middleware in the API middleware group
 *     → Did not work because at that point `user()` (from the token) is null.
 *  2. Appending this middleware in the API middleware group
 *     → Did not work because failed route binding still blocks the request
 *       before the middleware is entered.
 *
 * Given these limitations, the practical solution is to avoid relying on
 * route bindings in this context altogether. Instead, manually resolve
 * models inside controllers or services after this middleware runs,
 * ensuring that token activity is always updated consistently.
 */
class CheckTokenActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\JsonResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token) {
            $inactiveLimit = config('session_security.inactivity_timeout');
            $lastActivity = $token->last_activity_at ?? $token->created_at;

            if (now()->diffInMinutes($lastActivity, true) > $inactiveLimit) {
                $token->delete(); // force logout
                return JsonResponser::errorWithLog('Session expired due to inactivity.', null, 401);
            }

            // Note: Sanctum updates `last_used_at` on every request before hitting middleware,
            // so it's always recent here, making it unreliable for measuring inactivity.
            $token->forceFill(['last_activity_at' => now()])->save();
        }

        return $next($request);
    }
}
