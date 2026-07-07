<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __construct(protected AuthService $authService)
    {
    }
    public function __invoke(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'تم التسجيل بنجاح',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        ],201);
    }
}
