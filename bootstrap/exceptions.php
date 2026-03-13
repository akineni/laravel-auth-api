<?php

use App\Exceptions\Auth\AuthException;
use App\Exceptions\ConflictException;
use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return function ($exceptions) {

    $exceptions->render(function (AuthException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), $e->statusCode());
    });

    $exceptions->render(function (ConflictException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), 409);
    });
    
    $exceptions->render(function (ModelNotFoundException $e, Request $request) {
        $model = class_basename($e->getModel());

        return ApiResponse::error("{$model} not found.", 404);
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

    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        $previous = $e->getPrevious();

        if ($previous instanceof ModelNotFoundException) {
            $model = Str::headline(class_basename($previous->getModel()));

            return ApiResponse::error("{$model} not found.", 404);
        }

        return ApiResponse::error('Resource not found.', 404);
    });

    $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
        $requiredPermissions = method_exists($e, 'getRequiredPermissions') ? $e->getRequiredPermissions() : [];
        $message = $e->getMessage();

        if (!empty($requiredPermissions)) {
            $message .= " Required permission(s): " . implode(', ', $requiredPermissions);
        }

        Log::warning($message, ['permissions' => $requiredPermissions, 'exception' => $e]);

        return ApiResponse::error($message, 403, ['required_permissions' => $requiredPermissions]);
    });

    $exceptions->render(function (\App\Exceptions\ForbiddenException $e, Request $request) {
        $status = $e->getCode() ?: Response::HTTP_FORBIDDEN;
        Log::error($e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), $status);
    });

    $exceptions->render(function (\Illuminate\Database\UniqueConstraintViolationException $e, Request $request) {
        $previousMessage = $e->getPrevious()?->getMessage() ?? null;
        $message = $previousMessage ?: 'Duplicate entry detected';

        Log::error($message, ['exception' => $e]);
        return ApiResponse::error($message, Response::HTTP_CONFLICT);
    });

    // $exceptions->render(function (Throwable $e, Request $request) {
    //     $status = ($e instanceof HttpExceptionInterface)
    //         ? $e->getStatusCode()
    //         : ($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);

    //     $message = $e->getMessage() ?: 'Internal Server Error';

    //     Log::error($message, ['exception' => $e]);
    //     return ApiResponse::error($message, $status);
    // });
};
