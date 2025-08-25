<?php

namespace App\Policies;

use App\Enums\PermissionNames;
use App\Enums\Roles;
use App\Models\User;

/**
 * Class UserPolicy
 *
 * @package App\Policies
 */
class UserPolicy
{
    /**
     * Determine whether the user can view all users.
     *
     * @param  User  $user
     *
     * @return bool
     */
    public function viewAll(User $user): bool
    {
        return $user->can(PermissionNames::VIEW_ALL_USERS);
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionNames::CREATE_USER);
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  User  $user
     * @param  User  $loggedInUser
     *
     * @return bool
     */
    public function update(User $loggedInUser, User $user): bool
    {
        if (!$loggedInUser->can(PermissionNames::UPDATE_USER)) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN]) && $user->hasRole([Roles::AUTHOR])) {
            return true;
        }

        return $user->id === $loggedInUser->id;
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  User  $loggedInUser
     * @param  User  $user
     *
     * @return bool
     */
    public function show(User $loggedInUser, User $user): bool
    {
        if (!$loggedInUser->can(PermissionNames::VIEW_USER)) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN])) {
            return true;
        }

        return $user->id === $loggedInUser->id;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  User  $loggedInUser
     * @param  User  $user
     *
     * @return bool
     */
    public function delete(User $loggedInUser, User $user): bool
    {
        if (!$loggedInUser->can(PermissionNames::DELETE_USER)) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN]) && $user->hasRole([Roles::AUTHOR])) {
            return true;
        }

        return $user->id === $loggedInUser->id;
    }
}
