<?php

namespace App\Models;

use App\Enums\DbTables;
use App\Filters\TagFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 *
 * @package App\Models
 */
class Tag extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = DbTables::TAGS->value;

    /**
     * @var array
     */
    protected $fillable
        = [
            'name',
            'slug',
        ];

    /**
     * Filter the tags using the given filter.
     *
     * @param $query
     * @param $filter
     *
     * @return void
     */
    public function scopeFilter($query, $filter): void
    {
        tap(new TagFilter($query, $filter), function ($categoryFilter) {
            return $categoryFilter->apply();
        });
    }
}
