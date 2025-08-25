<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

/**
 * Class UserRepository
 *
 * @package App\Repositories\User
 */
class UserRepository extends BaseRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
}
