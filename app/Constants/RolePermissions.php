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
        PermissionNames::VIEW_ALL_USERS,
        PermissionNames::CREATE_USER,
        PermissionNames::VIEW_USER,
        PermissionNames::UPDATE_USER,
        PermissionNames::DELETE_USER,
    ];

    /**
     * const AUTHOR
     */
    public const AUTHOR = [
        PermissionNames::VIEW_USER,
        PermissionNames::UPDATE_USER,
        PermissionNames::DELETE_USER,
    ];
}
