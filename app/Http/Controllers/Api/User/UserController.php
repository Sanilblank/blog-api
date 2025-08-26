<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\General;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends BaseApiController
{
    /**
     * @param  UserService  $userService
     */
    public function __construct(protected UserService $userService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        try {
            $this->authorize('viewAll', User::class);

            $users = $this->userService->index(
                request: $request->validated(),
                with   : ['roles']
            )->paginate($request->per_page ?? General::DEFAULT_PAGINATION_LENGTH->value);

            return $this->success(
                __('Users retrieved successfully.'),
                UserResource::collection($users),
                new PaginationResource($users)
            );
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  UserCreateRequest  $request
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function store(UserCreateRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', User::class);

            \DB::beginTransaction();
            $this->userService->store($request->validated());
            \DB::commit();

            return $this->success(message: 'User created successfully.');
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UserUpdateRequest  $request
     * @param  User  $user
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        try {
            $this->authorize('update', [User::class, $user]);

            \DB::beginTransaction();
            $this->userService->update($user, $request->validated());
            \DB::commit();

            return $this->success(message: 'User updated successfully.');
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function show(User $user): JsonResponse
    {
        try {
            $this->authorize('show', [User::class, $user]);
            $user->load('roles');

            return $this->success(message: 'User retrieved successfully.', data: new UserResource($user));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->authorize('delete', [User::class, $user]);

            \DB::beginTransaction();
            $this->userService->destroy($user);
            \DB::commit();

            return $this->success(message: 'User deleted successfully.');
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }
}
