<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function sendResponse($data): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    protected function sendResponseError($error, $code = 404): JsonResponse
    {
        $response = [
            'error' => true,
            'message' => $error,
        ];

        if ($code == 0) $code = 500;

        return response()->json($response, $code);
    }

    protected function validator(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return Validator::make($data, $rules, $messages, $customAttributes);
    }

    abstract protected function rules();
    abstract protected function messages();
}
