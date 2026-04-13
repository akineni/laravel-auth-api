<?php

namespace App\Helpers;

use App\Http\Resources\ApiCollection;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function success($message = 'OK', $data = null, $code = Response::HTTP_OK)
    {
        $response = [
            'status'  => 'success',
            'message' => $message,
        ];

        if (!is_null($data)) {
            if ($data instanceof ApiCollection) {
                // Merge data/meta/links at the top level to avoid data.data nesting
                $resolved = $data->toArray(request());
                $response  = array_merge($response, $resolved);
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $code);
    }

    public static function error($message = 'Error', $code = Response::HTTP_BAD_REQUEST, $errors = null)
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}