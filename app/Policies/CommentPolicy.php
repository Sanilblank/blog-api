<?php

namespace App\Policies;

use App\Enums\PermissionNames;
use App\Enums\Roles;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

/**
 * Class CommentPolicy
 *
 * @package App\Policies
 */
class CommentPolicy
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
        return $user->can(PermissionNames::CREATE_COMMENT);
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  User  $loggedInUser
     * @param  Post  $post
     * @param  Comment  $comment
     *
     * @return bool
     */
    public function update(User $loggedInUser, Post $post, Comment $comment): bool
    {
        if (!$loggedInUser->can(PermissionNames::UPDATE_COMMENT)) {
            return false;
        }

        if ($post->id !== $comment->commentable_id) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN])) {
            return true;
        }

        return $comment->user_id === $loggedInUser->id;
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  User  $loggedInUser
     * @param  Post  $post
     * @param  Comment  $comment
     *
     * @return bool
     */
    public function delete(User $loggedInUser, Post $post, Comment $comment): bool
    {
        if (!$loggedInUser->can(PermissionNames::DELETE_COMMENT)) {
            return false;
        }

        if ($post->id !== $comment->commentable_id) {
            return false;
        }

        if ($loggedInUser->hasRole([Roles::ADMIN])) {
            return true;
        }

        if ($comment->user_id === $loggedInUser->id) {
            return true;
        }

        if ($comment->commentable_type === Post::class) {
            return $post->user_id === $loggedInUser->id;
        }

        return false;
    }
}
