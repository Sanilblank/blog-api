<?php

namespace App\Http\Controllers\Api\Comment;

use App\Enums\General;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Comment\CommentCreateRequest;
use App\Http\Requests\Comment\CommentIndexRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PaginationResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\Comment\CommentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class CommentController
 *
 * @package App\Http\Controllers\Api\Comment
 */
class CommentController extends BaseApiController
{
    /**
     * CommentController constructor.
     *
     * @param  CommentService  $commentService
     */
    public function __construct(protected CommentService $commentService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param  CommentIndexRequest  $request
     * @param  Post  $post
     *
     * @return JsonResponse
     */
    public function index(CommentIndexRequest $request, Post $post): JsonResponse
    {
        try {
            $comments = $this->commentService->index(
                request: $request->validated(),
                with   : ['author'],
                where  : ['commentable_type' => Post::class, 'commentable_id' => $post->id]
            )->paginate($request->per_page ?? General::DEFAULT_PAGINATION_LENGTH->value);

            return $this->success(
                __('Comments fetched successfully'),
                CommentResource::collection($comments),
                new PaginationResource($comments)
            );
        } catch (\Throwable $e) {
            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CommentCreateRequest  $request
     * @param  Post  $post
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function store(CommentCreateRequest $request, Post $post): JsonResponse
    {
        try {
            $this->authorize('create', Comment::class);
            DB::beginTransaction();
            $comment = $this->commentService->create($post, $request->validated());
            $comment->load(['author']);
            DB::commit();

            return $this->success(message: __('Comment created successfully'), data: new CommentResource($comment));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Post  $post
     * @param  Comment  $comment
     *
     * @return JsonResponse
     */
    public function show(Post $post, Comment $comment): JsonResponse
    {
        try {
            if ($post->id !== $comment->commentable_id) {
                throw new AuthorizationException(__('This action is unauthorized.'));
            }

            $comment->load(['author']);

            return $this->success(
                __('Comment retrieved successfully.'),
                new CommentResource($comment)
            );
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CommentCreateRequest  $request
     * @param  Post  $post
     * @param  Comment  $comment
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function update(CommentCreateRequest $request, Post $post, Comment $comment): JsonResponse
    {
        try {
            $this->authorize('update', [Comment::class, $post, $comment]);
            DB::beginTransaction();
            $updatedComment = $this->commentService->update($comment, $request->validated());
            $updatedComment->load(['author']);
            DB::commit();

            return $this->success(message: __('Comment updated successfully'), data: new CommentResource($updatedComment));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Post  $post
     * @param  Comment  $comment
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function destroy(Post $post, Comment $comment): JsonResponse
    {
        try {
            $this->authorize('delete', [Comment::class, $post, $comment]);
            DB::beginTransaction();
            $this->commentService->delete($comment);
            DB::commit();

            return $this->success(message: __('Comment deleted successfully'));
        } catch (AuthorizationException $e) {
            return $this->failure(message: $e->getMessage(), code: 403);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e);

            return $this->failure(__($e->getMessage()));
        }
    }
}
