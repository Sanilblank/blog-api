<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserFilter
 */
class UserFilter extends BaseFilter
{
    /**
     * Searches inside the data.
     *
     * @param  string  $search
     *
     * @return Builder
     */
    public function search(string $search): Builder
    {
        return $this->builder->whereAny(['name', 'email'], 'ilike', "%$search%");
    }

    /**
     * Filters the users by roles.
     *
     * @param array $roles The names of the roles to filter by.
     *
     * @return Builder
     */
    public function roles(array $roles): Builder
    {
        return $this->builder->whereHas('roles', function (Builder $query) use ($roles) {
            $query->whereIn('name', $roles);
        });
    }
}
