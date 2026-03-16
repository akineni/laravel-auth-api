<?php

use App\Exceptions\Auth\AuthException;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return function ($exceptions) {

    $exceptions->render(function (AuthException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), $e->statusCode());
    });

    $exceptions->render(function (ConflictException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), 409);
    });

    $exceptions->render(function (ForbiddenException $e, Request $request) {
        return ApiResponse::error(
            $e->getMessage() ?: 'Forbidden.',
            $e->getCode() ?: 403
        );
    });

    $exceptions->render(function (UnauthorizedException $e, Request $request) {
        $requiredPermissions = method_exists($e, 'getRequiredPermissions')
            ? $e->getRequiredPermissions()
            : [];

        $message = !empty($requiredPermissions)
            ? 'You do not have the required permission(s): ' . implode(', ', $requiredPermissions) . '.'
            : 'You are not authorized to perform this action.';

        return ApiResponse::error($message, 403, [
            'required_permissions' => $requiredPermissions,
        ]);
    });

    $exceptions->render(function (ModelNotFoundException $e, Request $request) {
        $model = Str::headline(class_basename($e->getModel()));

        return ApiResponse::error("{$model} not found.", 404);
    });

    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        $previous = $e->getPrevious();

        if ($previous instanceof ModelNotFoundException) {
            $model = Str::headline(class_basename($previous->getModel()));

            return ApiResponse::error("{$model} not found.", 404);
        }

        return ApiResponse::error('Resource not found.', 404);
    });

    $exceptions->render(function (ValidationException $e, Request $request) {
        $firstError = collect($e->errors())->flatten()->first();

        return ApiResponse::error(
            $firstError ?: 'The given data was invalid.',
            $e->status,
            $e->errors()
        );
    });

    $exceptions->render(function (AuthenticationException $e, Request $request) {
        return ApiResponse::error(
            $e->getMessage() ?: 'Unauthenticated.',
            401
        );
    });

    $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
        return ApiResponse::error(
            'A record with the same unique value already exists.',
            409
        );
    });

    $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
        $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

        $message = $retryAfter
            ? "Too many attempts. Please try again in {$retryAfter} seconds."
            : 'Too many attempts. Please try again later.';

        return ApiResponse::error($message, 429);
    });

    /*
    |--------------------------------------------------------------------------
    | Fallback Exception Handler
    |--------------------------------------------------------------------------
    |
    | This must remain the LAST exception renderer. It acts as a catch-all
    | for any unhandled exceptions in the application. Any handlers placed
    | after this will never be reached.
    |
    */
    $exceptions->render(function (Throwable $e, Request $request) {
        Log::error($e->getMessage(), ['exception' => $e]);

        return ApiResponse::error('Internal Server Error', 500);
    });
};