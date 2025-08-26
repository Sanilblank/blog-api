<?php

namespace App\Policies;

use App\Enums\PermissionNames;
use App\Enums\Roles;
use App\Models\Post;
use App\Models\User;

/**
 * Class PostPolicy
 *
 * @package App\Policies
 */
class PostPolicy
{
    /**
     * Determine whether the user can create posts.
     *
     * @param  User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionNames::CREATE_POST);
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  User  $loggedInUser
     * @param  Post  $post
     *
     * @return bool
     */
    public function update(User $loggedInUser, Post $post): bool
    {
        if (!$loggedInUser->can(PermissionNames::UPDATE_POST)) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN])) {
            return true;
        }

        return $post->user_id === $loggedInUser->id;
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  User  $loggedInUser
     * @param  Post  $post
     *
     * @return bool
     */
    public function delete(User $loggedInUser, Post $post): bool
    {
        if (!$loggedInUser->can(PermissionNames::DELETE_POST)) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN])) {
            return true;
        }

        return $post->user_id === $loggedInUser->id;
    }
}
