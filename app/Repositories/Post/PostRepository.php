<?php

namespace App\Repositories\Post;

use App\Models\Post;
use App\Repositories\BaseRepository;

/**
 * Class PostRepository
 *
 * @package App\Repositories\Post
 */
class PostRepository extends BaseRepository
{
    /**
     * Constructor
     *
     * @param  Post  $post  Eloquent model for the repository
     */
    public function __construct(Post $post)
    {
        parent::__construct($post);
    }
}
