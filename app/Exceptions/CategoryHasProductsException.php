<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CategoryHasProductsException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], 409); // 409 Conflict — أنسب Status Code لتعارض البيانات
    }
}
