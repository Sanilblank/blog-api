<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Repositories\Category\CategoryRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class CategoryService
 *
 * @package App\Services\Category
 */
class CategoryService
{
    /**
     * @param  CategoryRepository  $categoryRepository
     */
    public function __construct(protected CategoryRepository $categoryRepository)
    {
        //
    }

    /**
     * Create a new category.
     *
     * @param  array  $data
     *
     * @return Category
     */
    public function create(array $data): Category
    {
        return $this->categoryRepository->create([
            'name' => Arr::get($data, 'name'),
            'slug' => Str::slug(Arr::get($data, 'name')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Category  $category
     * @param  array  $data
     *
     * @return Model
     */
    public function update(Category $category, array $data): Model
    {
        return $this->categoryRepository->update($category, [
            'name' => Arr::get($data, 'name'),
            'slug' => Str::slug(Arr::get($data, 'name')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category  $category
     *
     * @return bool
     */
    public function delete(Category $category): bool
    {
        return $this->categoryRepository->delete($category);
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
        return $this->categoryRepository->index($request, $with, $where);
    }
}