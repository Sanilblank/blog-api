<?php

namespace App\Services\Tag;

use App\Models\Tag;
use App\Repositories\Tag\TagRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class TagService
 *
 * @package App\Services\Tag
 */
class TagService
{
    /**
     * @param  TagRepository  $tagRepository
     */
    public function __construct(protected TagRepository $tagRepository)
    {
        //
    }

    /**
     * Create a new tag.
     *
     * @param  array  $data
     *
     * @return Tag
     */
    public function create(array $data): Tag
    {
        return $this->tagRepository->create([
            'name' => Arr::get($data, 'name'),
            'slug' => Str::slug(Arr::get($data, 'name')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Tag  $tag
     * @param  array  $data
     *
     * @return Model
     */
    public function update(Tag $tag, array $data): Model
    {
        return $this->tagRepository->update($tag, [
            'name' => Arr::get($data, 'name'),
            'slug' => Str::slug(Arr::get($data, 'name')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Tag  $tag
     *
     * @return bool
     */
    public function delete(Tag $tag): bool
    {
        return $this->tagRepository->delete($tag);
    }

    /**
     * Return a query builder for the given request, with and where conditions
     *
     * @param array $request
     * @param array $with
     * @param array $where
     *
     * @return Builder
     */
    public function index(array $request = [], array $with = [], array $where = []): Builder
    {
        return $this->tagRepository->index($request, $with, $where);
    }
}