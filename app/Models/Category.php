<?php

namespace App\Models;

use App\Enums\DbTables;
use App\Filters\CategoryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * @package App\Models
 */
class Category extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = DbTables::CATEGORIES->value;

    /**
     * @var array
     */
    protected $fillable
        = [
            'name',
            'slug',
        ];

    /**
     * Filter the categories using the given filter.
     *
     * @param $query
     * @param $filter
     *
     * @return void
     */
    public function scopeFilter($query, $filter): void
    {
        tap(new CategoryFilter($query, $filter), function ($categoryFilter) {
            return $categoryFilter->apply();
        });
    }
}
