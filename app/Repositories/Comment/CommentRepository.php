<?php

namespace App\Repositories\Comment;

use App\Models\Comment;
use App\Repositories\BaseRepository;

/**
 * Class CommentRepository
 *
 * @package App\Repositories\Comment
 */
class CommentRepository extends BaseRepository
{
    /**
     * Constructor
     *
     * @param  Comment  $comment  Eloquent model for the repository
     */
    public function __construct(Comment $comment)
    {
        parent::__construct($comment);
    }
}
