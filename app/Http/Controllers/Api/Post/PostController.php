<?php

namespace App\Http\Controllers\Api\Post;

use App\Enums\General;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostCreateRequest;
use App\Http\Requests\Post\PostIndexRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\Post\PostService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class PostController
 *
 * @package App\Http\Controllers\Api\Post
 */
class PostController extends BaseApiController
{
    /**
     * PostController constructor.
     *
     * @param  PostService  $postService
     */
    public function __construct(protected PostService $postService)
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param  PostIndexRequest  $request
     *
     * @return JsonResponse
     */
    public function index(PostIndexRequest $request): JsonResponse
    {
        try {
            $posts = $this->postService->index(
                request: $request->validated(),
                with: ['author', 'category', 'tags']
            )->paginate($request->per_page ?? General::DEFAULT_PAGINATION_LENGTH->value);

            return $this->success(
                __('Posts fetched successfully'),
                PostResource::collection($posts),
                new PaginationResource($posts)
            );
        } catch (\Throwable $e) {
            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PostCreateRequest  $request
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function store(PostCreateRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Post::class);
            DB::beginTransaction();
            $this->postService->create($request->validated());
            DB::commit();

            return $this->success(message: __('Post created successfully'));
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
     * @param  Post  $post
     *
     * @return JsonResponse
     */
    public function show(Post $post): JsonResponse
    {
        try {
            $post->load(['author', 'category', 'tags']);

            return $this->success(
                __('Post retrieved successfully.'),
                new PostResource($post)
            );
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PostCreateRequest  $request
     * @param  Post  $post
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function update(PostCreateRequest $request, Post $post): JsonResponse
    {
        try {
            $this->authorize('update', [Post::class, $post]);
            DB::beginTransaction();
            $this->postService->update($post, $request->validated());
            DB::commit();

            return $this->success(message: __('Post updated successfully'));
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
     * @param  Post  $post
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function destroy(Post $post): JsonResponse
    {
        try {
            $this->authorize('delete', [Post::class, $post]);
            DB::beginTransaction();
            $this->postService->delete($post);
            DB::commit();

            return $this->success(message: __('Post deleted successfully'));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }
}
