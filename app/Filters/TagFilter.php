<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class TagFilter
 *
 * @package App\Filters
 */
class TagFilter extends BaseFilter
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
        return $this->builder->whereAny(['name', 'slug'], 'ilike', "%$search%");
    }
}
