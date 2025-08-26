<?php

namespace App\Services\Comment;

use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Comment\CommentRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Class PostService
 *
 * @package App\Services\Post
 */
class CommentService
{
    /**
     * @param  CommentRepository  $commentRepository
     */
    public function __construct(protected CommentRepository $commentRepository)
    {
        //
    }

    /**
     * Create a new comment instance.
     *
     * @param  Post  $post
     * @param  array  $data
     *
     * @return Comment
     */
    public function create(Post $post, array $data): Comment
    {
        return $post->comments()->create([
            'user_id' => auth()->user()->id,
            'body'    => Arr::get($data, 'body'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Comment  $comment
     * @param  array  $data
     *
     * @return Model
     */
    public function update(Comment $comment, array $data): Model
    {
        return $this->commentRepository->update($comment, $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Comment  $comment
     *
     * @return bool
     */
    public function delete(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }

    /**
     * Return a query builder for the given request, with and where conditions
     *
     * @param  array  $request
     * @param  array  $with
     * @param  array  $where
     *
     * @return Builder
     */
    public function index(array $request = [], array $with = [], array $where = []): Builder
    {
        return $this->commentRepository->index($request, $with, $where);
    }
}