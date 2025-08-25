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
        PermissionNames::CREATE_USER,
        PermissionNames::VIEW_USER,
        PermissionNames::DELETE_USER,
        PermissionNames::UPDATE_USER,
    ];

    /**
     * const AUTHOR
     */
    public const AUTHOR = [
    ];
}
