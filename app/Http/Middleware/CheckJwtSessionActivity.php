<?php

namespace App\Http\Middleware;

use App\Exceptions\Auth\InvalidSessionException;
use App\Exceptions\Auth\SessionExpiredException;
use App\Helpers\ApiResponse;
use App\Services\Auth\AuthSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckJwtSessionActivity
{
    public function __construct(
        private readonly AuthSessionService $authSessionService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $sessionId = JWTAuth::parseToken()->getPayload()->get('sid');

        if (!$sessionId) {
            return ApiResponse::error('Invalid session.', 401);
        }

        try {
            $session = $this->authSessionService->validate(
                userId: (string) $user->getKey(),
                sessionId: (string) $sessionId
            );
        } catch (SessionExpiredException|InvalidSessionException $e) {
            try {
                JWTAuth::invalidate(JWTAuth::getToken());
            } catch (\Throwable $throwable) {
            }

            return ApiResponse::error($e->getMessage(), 401);
        }

        $this->authSessionService->touch($session, $request);

        return $next($request);
    }
}
