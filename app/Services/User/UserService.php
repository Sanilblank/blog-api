<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UserService
{
    /**
     * @param  UserRepository  $userRepository
     */
    public function __construct(protected UserRepository $userRepository)
    {
        //
    }

    /**
     * Return a query builder for the given request, with and where conditions
     *
     * @param array $request
     * @param array $with
     * @param array $where
     *
     * @return Builder
     */
    public function index(array $request = [], array $with = [], array $where = []): Builder
    {
        return $this->userRepository->index($request, $with, $where);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  array  $data
     *
     * @return User
     */
    public function store(array $data): User
    {
        $user = $this->userRepository->create([
            'name' => Arr::get($data, 'name'),
            'email' => Arr::get($data, 'email'),
            'password' => Arr::get($data, 'password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole(Arr::get($data, 'role'));

        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  User  $user
     * @param  array  $data
     *
     * @return Model
     */
    public function update(User $user, array $data): Model
    {
        return $this->userRepository->update($user, $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     *
     * @return bool
     */
    public function destroy(User $user): bool
    {
        return $this->userRepository->delete($user);
    }
}