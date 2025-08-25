<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LoginResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseApiController
{
    /**
     * Handle an authentication attempt.
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('Invalid credentials')],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success(__('Logged in successfully!'), [
            'user'  => new LoginResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Logout the user and delete the related token.
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(__('Logged out successfully!'));
    }
}
