<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class PostFilter
 *
 * @package App\Filters
 */
class PostFilter extends BaseFilter
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
        return $this->builder->where(function ($query) use ($search) {
            $query->where('title', 'ilike', "%$search%")
                  ->orWhereHas('author', function ($q) use ($search) {
                      $q->where('name', 'ilike', "%$search%")
                        ->orWhere('email', 'ilike', "%$search%");
                  })
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name', 'ilike', "%$search%")
                        ->orWhere('slug', 'ilike', "%$search%");
                  })
                  ->orWhereHas('tags', function ($q) use ($search) {
                      $q->where('name', 'ilike', "%$search%")
                        ->orWhere('slug', 'ilike', "%$search%");
                  });
        });
    }

    /**
     * Filters by category.
     *
     * @param  int  $category_id
     *
     * @return Builder
     */
    public function category(int $category_id): Builder
    {
        return $this->builder->where('category_id', $category_id);
    }

    /**
     * Filters by tags.
     *
     * @param  array  $tags
     *
     * @return Builder
     */
    public function tags(array $tags): Builder
    {
        return $this->builder->whereHas('tags', function ($query) use ($tags) {
            $query->whereIn('tags.id', $tags);
        });
    }

    /**
     * Filters by author.
     */
    public function author(int $author_id): Builder
    {
        return $this->builder->where('user_id', $author_id);
    }
}
