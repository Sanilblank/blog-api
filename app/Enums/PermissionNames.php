<?php

namespace App\Enums;

/**
 * PermissionNames enum
 */
enum PermissionNames: string
{
    case CREATE_USER = 'create_user';
    case VIEW_USER   = 'view_user';
    case DELETE_USER = 'delete_user';
    case UPDATE_USER = 'update_user';
}
