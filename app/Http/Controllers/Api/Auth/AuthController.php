<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\Roles;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\LoginResource;
use App\Services\User\UserService;
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
     * AuthController constructor.
     *
     * @param  UserService  $userService
     */
    public function __construct(protected UserService $userService)
    {
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  LoginRequest  $request
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (!Auth::attempt($request->validated())) {
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
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $e) {
            return $this->failure($e->getMessage());
        }
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
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->success(__('Logged out successfully!'));
        } catch (\Throwable $e) {
            return $this->failure($e->getMessage());
        }
    }

    /**
     * Register a new author.
     *
     * @param  RegisterRequest  $request
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            \DB::beginTransaction();
            $data = $request->validated();
            $data['role'] = Roles::AUTHOR->value;
            $user = $this->userService->store($data);
            $token = $user->createToken('api-token')->plainTextToken;
            \DB::commit();

            return $this->success(__('Author registered successfully!'), [
                'user'  => new LoginResource($user),
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            \DB::rollBack();

            return $this->failure($e->getMessage());
        }
    }
}
