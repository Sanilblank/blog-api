<?php

namespace App\Models;

use App\Enums\DbTables;
use App\Filters\CommentFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Comment
 *
 * @package App\Models
 */
class Comment extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = DbTables::COMMENTS->value;

    /**
     * @var array
     */
    protected $fillable
        = [
            'user_id',
            'body',
        ];

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Filter the comments using the given filter.
     *
     * @param $query
     * @param $filter
     *
     * @return void
     */
    public function scopeFilter($query, $filter): void
    {
        tap(new CommentFilter($query, $filter), function ($commentFilter) {
            return $commentFilter->apply();
        });
    }
}
