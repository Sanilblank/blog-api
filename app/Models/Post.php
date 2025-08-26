<?php

namespace App\Models;

use App\Enums\DbTables;
use App\Filters\PostFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class Post
 *
 * @package App\Models
 */
class Post extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = DbTables::POSTS->value;

    /**
     * @var array
     */
    protected $fillable
        = [
            'user_id',
            'category_id',
            'title',
            'body',
        ];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (Post $post) {
            $post->tags()->detach();
        });
    }

    /**
     * A post belongs to a user.
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * A post belongs to a category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * A post can have many tags.
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Filter the posts using the given filter.
     *
     * @param $query
     * @param $filter
     *
     * @return void
     */
    public function scopeFilter($query, $filter): void
    {
        tap(new PostFilter($query, $filter), function ($postFilter) {
            return $postFilter->apply();
        });
    }

    /**
     * A post can have many comments.
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
