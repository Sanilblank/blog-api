<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class PostFilter
 *
 * @package App\Filters
 */
class CommentFilter extends BaseFilter
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
            $query->where('body', 'ilike', "%$search%")
                  ->orWhereHas('author', function ($q) use ($search) {
                      $q->where('name', 'ilike', "%$search%")
                        ->orWhere('email', 'ilike', "%$search%");
                  });
        });
    }

   /**
     * Filters comments by author.
     *
     * @param int $id The ID of the author to filter by.
     *
     * @return Builder
     */
   public function author(int $id): Builder
   {
       return $this->builder->where('user_id', $id);
   }
}
