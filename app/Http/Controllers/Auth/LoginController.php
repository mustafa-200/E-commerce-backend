<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __construct(protected AuthService $authService)
    {
    }
    public function __invoke(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        ],200);
    }
}
