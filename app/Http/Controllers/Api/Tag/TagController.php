<?php

namespace App\Http\Controllers\Api\Tag;

use App\Enums\General;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Tag\TagCreateRequest;
use App\Http\Requests\Tag\TagIndexRequest;
use App\Http\Requests\Tag\TagUpdateRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\Tag\TagService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * Class TagController
 *
 * @package App\Http\Controllers\Api\Tag
 */
class TagController extends BaseApiController
{
    /**
     * TagController constructor.
     *
     * @param  TagService  $tagService
     */
    public function __construct(protected TagService $tagService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(TagIndexRequest $request): JsonResponse
    {
        try {
            $tags = $this->tagService->index(
                request: $request->validated(),
            )->paginate($request->per_page ?? General::DEFAULT_PAGINATION_LENGTH->value);

            return $this->success(
                __('Tags fetched successfully'),
                TagResource::collection($tags),
                new PaginationResource($tags)
            );
        } catch (\Throwable $e) {
            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  TagCreateRequest  $request
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function store(TagCreateRequest $request): JsonResponse
    {
        try {
            $this->tagService->create($request->validated());

            return $this->success(message: __('Tag created successfully'));
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
     * @param  Tag  $tag
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function show(Tag $tag): JsonResponse
    {
        try {
            return $this->success(message: __('Tag details.'), data: new TagResource($tag));
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  TagUpdateRequest  $request
     * @param  Tag  $tag
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function update(TagUpdateRequest $request, Tag $tag): JsonResponse
    {
        try {
            $this->tagService->update($tag, $request->validated());

            return $this->success(message: __('Tag updated successfully'));
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
     * @param  Tag  $tag
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function destroy(Tag $tag): JsonResponse
    {
        try {
            $this->tagService->delete($tag);

            return $this->success(message: __('Tag deleted successfully'));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }
}
