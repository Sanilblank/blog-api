<?php

namespace App\Enums;

/**
 * DbTables enum
 */
enum DbTables: string
{
    case USERS = 'users';

    case CATEGORIES = 'categories';

    case TAGS = 'tags';

    case POSTS = 'posts';

    case TAGGABLES = 'taggables';
}
