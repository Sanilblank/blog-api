<?php

namespace App\Http\Controllers\Api\Category;

use App\Enums\General;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Category\CategoryCreateRequest;
use App\Http\Requests\Category\CategoryIndexRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PaginationResource;
use App\Models\Category;
use App\Services\Category\CategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CategoryController
 *
 * @package App\Http\Controllers\Api
 */
class CategoryController extends BaseApiController
{
    /**
     * CategoryController constructor.
     *
     * @param  CategoryService  $categoryService
     */
    public function __construct(protected CategoryService $categoryService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param  CategoryIndexRequest  $request
     *
     * @return JsonResponse
     */
    public function index(CategoryIndexRequest $request): JsonResponse
    {
        try {
            $categories = $this->categoryService->index(
                request: $request->validated(),
            )->paginate($request->per_page ?? General::DEFAULT_PAGINATION_LENGTH->value);

            return $this->success(
                __('Categories fetched successfully'),
                CategoryResource::collection($categories),
                new PaginationResource($categories)
            );
        } catch (\Throwable $e) {
            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CategoryCreateRequest  $request
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function store(CategoryCreateRequest $request): JsonResponse
    {
        try {
            $this->categoryService->create($request->validated());

            return $this->success(message: __('Category created successfully'));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Category  $category
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function show(Category $category): JsonResponse
    {
        try {
            return $this->success(message: __('Category details.'), data: new CategoryResource($category));
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CategoryUpdateRequest  $request
     * @param  Category  $category
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        try {
            $this->categoryService->update($category, $request->validated());

            return $this->success(message: __('Category updated successfully'));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category  $category
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->delete($category);

            return $this->success(message: __('Category deleted successfully'));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }
}
