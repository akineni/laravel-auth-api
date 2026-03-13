<?php

use App\Exceptions\Auth\AuthException;
use App\Helpers\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Facades\Log;

return function ($exceptions) {

    $exceptions->render(function (AuthException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), $e->statusCode());
    });
    
    $exceptions->render(function (ModelNotFoundException $e, Request $request) {
        $model = class_basename($e->getModel());

        return ApiResponse::error("{$model} not found.", 404);
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

    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
        Log::warning("Resource not found", ['exception' => $e]);
        return ApiResponse::error("Resource not found", 404);
    });

    $exceptions->render(function (\App\Exceptions\ConflictException $e, Request $request) {
        $status = $e->getCode() ?: Response::HTTP_CONFLICT;
        Log::error($e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), $status);
    });

    $exceptions->render(function (\App\Exceptions\ForbiddenException $e, Request $request) {
        $status = $e->getCode() ?: Response::HTTP_FORBIDDEN;
        Log::error($e->getMessage(), ['exception' => $e]);
        return ApiResponse::error($e->getMessage(), $status);
    });

    $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, Request $request) {
        $status = $e instanceof HttpExceptionInterface
            ? $e->getStatusCode()
            : Response::HTTP_UNAUTHORIZED;

        $message = $e->getMessage() ?: Response::$statusTexts[$status] ?? 'Unauthorized';

        Log::error($message, ['exception' => $e]);
        return ApiResponse::error($message, $status);
    });

    $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
        $status = $e->status ?: 422;
        $message = $e->getMessage() ?: 'Validation failed';

        Log::info($message, ['errors' => $e->errors()]);
        return ApiResponse::error($message, $status, $e->errors());
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
