<?php

namespace App\Constants;

use App\Enums\PermissionNames;

/**
 * Class RolePermissions
 */
class RolePermissions
{
    /**
     * const ADMIN
     */
    public const ADMIN = [
        /**
         * User Permissions
         */
        PermissionNames::VIEW_ALL_USERS,
        PermissionNames::CREATE_USER,
        PermissionNames::VIEW_USER,
        PermissionNames::UPDATE_USER,
        PermissionNames::DELETE_USER,

        /**
         * Post Permissions
         */
        PermissionNames::CREATE_POST,
        PermissionNames::UPDATE_POST,
        PermissionNames::DELETE_POST,

        /**
         * Comment Permissions
         */
        PermissionNames::CREATE_COMMENT,
        PermissionNames::UPDATE_COMMENT,
        PermissionNames::DELETE_COMMENT,
    ];

    /**
     * const AUTHOR
     */
    public const AUTHOR = [
        /**
         * User Permissions
         */
        PermissionNames::VIEW_USER,
        PermissionNames::UPDATE_USER,
        PermissionNames::DELETE_USER,

        /**
         * Post Permissions
         */
        PermissionNames::CREATE_POST,
        PermissionNames::UPDATE_POST,
        PermissionNames::DELETE_POST,

        /**
         * Comment Permissions
         */
        PermissionNames::CREATE_COMMENT,
        PermissionNames::UPDATE_COMMENT,
        PermissionNames::DELETE_COMMENT,
    ];
}
