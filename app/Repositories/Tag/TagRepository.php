<?php

namespace App\Repositories\Tag;

use App\Models\Category;
use App\Models\Tag;
use App\Repositories\BaseRepository;

/**
 * Class TagRepository
 *
 * @package App\Repositories\Tag
 */
class TagRepository extends BaseRepository
{
    /**
     * Constructor
     *
     * @param Tag $tag Eloquent model for the repository
     */
    public function __construct(Tag $tag)
    {
        parent::__construct($tag);
    }
}
