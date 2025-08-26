<?php

namespace App\Services\Post;

use App\Models\Category;
use App\Models\Post;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Post\PostRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class PostService
 *
 * @package App\Services\Post
 */
class PostService
{
    /**
     * @param  PostRepository  $postRepository
     */
    public function __construct(protected PostRepository $postRepository)
    {
        //
    }

    /**
     * Create a new post instance.
     *
     * @param  array  $data
     *
     * @return Post
     */
    public function create(array $data): Post
    {
        $data['user_id'] = auth()->user()->id;
        $post = $this->postRepository->create($data);

        if (!empty(Arr::get($data, 'tags'))) {
            $post->tags()->sync($data['tags']);
        }

        return $post;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Post  $post
     * @param  array  $data
     *
     * @return Post
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        if (!empty(Arr::get($data, 'tags'))) {
            $post->tags()->sync($data['tags']);
        }

        return $post;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Post  $post
     *
     * @return bool
     */
    public function delete(Post $post): bool
    {
        return $this->postRepository->delete($post);
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
        return $this->postRepository->index($request, $with, $where);
    }
}