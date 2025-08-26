<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

uses()->group('comment');

/**
 * Create Comment
 */
it('users can create a comment', function (User $user, Post $post) {
    $this->actingAs($user, 'sanctum');

    $payload = ['body' => 'This is a test comment'];

    $this->postJson(route('posts.comments.store', $post), $payload)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Comment created successfully'))
                                                       ->has('data')
         );

    $this->assertDatabaseHas('comments', [
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
        'user_id'          => $user->id,
        'body'             => 'This is a test comment',
    ]);
})->with([
    fn() => [asAdmin(), Post::factory()->create()],
    fn() => [asAuthor(), Post::factory()->create()],
]);

it('fails validation if body is missing', function (User $admin, Post $post) {
    $this->actingAs($admin, 'sanctum');

    $this->postJson(route('posts.comments.store', $post), [])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['body']);

    $this->assertDatabaseMissing('comments', [
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
        'user_id'          => $admin->id,
    ]);
})->with([
    fn() => [asAdmin(), Post::factory()->create()],
]);

it('guest cannot create a comment', function (Post $post) {
    $payload = ['body' => 'Guest comment'];

    $this->postJson(route('posts.comments.store', $post), $payload)
         ->assertUnauthorized()
         ->assertJson(fn(AssertableJson $json) => $json->where('message', __('Unauthenticated.'))
         );

    $this->assertDatabaseMissing('comments', [
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
        'body'             => 'Guest comment',
    ]);
})->with([
    fn() => Post::factory()->create(),
]);

/**
 * Update Comment
 */
it('admin can update a comment on the post', function (User $admin, Post $post, Comment $comment) {
    $this->actingAs($admin, 'sanctum');

    $payload = ['body' => 'Updated by Admin'];

    $this->putJson(route('posts.comments.update', [$post, $comment]), $payload)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Comment updated successfully'))
                                                       ->has('data')
         );

    $this->assertDatabaseHas('comments', [
        'id'   => $comment->id,
        'body' => 'Updated by Admin',
    ]);
})->with([
    fn() => (static function () {
        $admin = asAdmin();
        $post = Post::factory()->create();
        $comment = Comment::factory()->forPost($post)->create();

        return [$admin, $post, $comment];
    })(),
]);

it('comment owner can update their own comment on the post', function (User $author, Post $post, Comment $comment) {
    $this->actingAs($author, 'sanctum');

    $payload = ['body' => 'Updated by Owner'];

    $this->putJson(route('posts.comments.update', [$post, $comment]), $payload)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Comment updated successfully'))
                                                       ->has('data')
         );

    $this->assertDatabaseHas('comments', [
        'id'   => $comment->id,
        'body' => 'Updated by Owner',
    ]);
})->with([
    fn() => (static function () {
        $author = asAuthor();
        $post = Post::factory()->create();
        $comment = Comment::factory()->forPost($post)->byUser($author)->create(['body' => 'Original Body']);

        return [$author, $post, $comment];
    })(),
]);

it(
    'non-owner cannot update another users comment on the same post',
    function (User $nonOwner, Post $post, Comment $comment) {
        $this->actingAs($nonOwner, 'sanctum');

        $payload = ['body' => 'Hacked Update'];

        $this->putJson(route('posts.comments.update', [$post, $comment]), $payload)
             ->assertForbidden()
             ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                           ->where('message', __('This action is unauthorized.'))
             );

        $this->assertDatabaseHas('comments', [
            'id'   => $comment->id,
            'body' => $comment->body,
        ]);
    }
)->with([
    fn() => (static function () {
        $owner = asAuthor();
        $nonOwner = asAuthor();
        $post = Post::factory()->create();
        $comment = Comment::factory()->forPost($post)->byUser($owner)->create(['body' => 'Owner Body']);
        return [$nonOwner, $post, $comment];
    })(),
]);

it(
    'cannot update comment when post route param does not match comment.commentable_id',
    function (User $author, Post $post, Post $otherPost, Comment $comment) {
        $this->actingAs($author, 'sanctum');

        $payload = ['body' => 'Invalid Update'];

        $this->putJson(route('posts.comments.update', [$post, $comment]), $payload)
             ->assertForbidden()
             ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                           ->where('message', __('This action is unauthorized.'))
             );

        $this->assertDatabaseHas('comments', [
            'id'   => $comment->id,
            'body' => $comment->body,
        ]);
    }
)->with([
    fn() => (static function () {
        $author = asAuthor();
        $post = Post::factory()->create();
        $otherPost = Post::factory()->create();
        $comment = Comment::factory()->forPost($otherPost)->byUser($author)->create(['body' => 'Original Body']);

        return [$author, $post, $otherPost, $comment];
    })(),
    fn() => (static function () {
        $author = asAdmin();
        $post = Post::factory()->create();
        $otherPost = Post::factory()->create();
        $comment = Comment::factory()->forPost($otherPost)->byUser($author)->create(['body' => 'Original Body']);

        return [$author, $post, $otherPost, $comment];
    })(),
]);

it('fails validation when body is missing on update', function (User $author, Post $post, Comment $comment) {
    $this->actingAs($author, 'sanctum');

    $this->putJson(route('posts.comments.update', [$post, $comment]), [])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['body']);

    $this->assertDatabaseHas('comments', [
        'id'   => $comment->id,
        'body' => $comment->body,
    ]);
})->with([
    fn() => (static function () {
        $author = asAuthor();
        $post = Post::factory()->create();
        $comment = Comment::factory()->forPost($post)->byUser($author)->create(['body' => 'Original Body']);

        return [$author, $post, $comment];
    })(),
]);

it('guest cannot update a comment', function (Post $post, Comment $comment) {
    $payload = ['body' => 'Guest Update'];

    $this->putJson(route('posts.comments.update', [$post, $comment]), $payload)
         ->assertUnauthorized()
         ->assertJson(fn(AssertableJson $json) => $json->where('message', __('Unauthenticated.')));

    $this->assertDatabaseHas('comments', [
        'id'   => $comment->id,
        'body' => $comment->body,
    ]);
})->with([
    fn() => (static function () {
        $post = Post::factory()->create();
        $comment = Comment::factory()->forPost($post)->create(['body' => 'Original Body']);

        return [$post, $comment];
    })(),
]);

/**
 * Delete Comment
 */
it('admin can delete any comment', function (User $admin, Post $post) {
    $comment = Comment::factory()->for($post, 'commentable')->create();

    $this->actingAs($admin, 'sanctum');

    $this->deleteJson(route('posts.comments.destroy', [$post, $comment]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Comment deleted successfully'))
         );

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
})->with([
    fn() => [asAdmin(), Post::factory()->create()],
]);

it('post author can delete a comment on their post', function (User $author, Post $post) {
    $post->update(['user_id' => $author->id]);
    $comment = Comment::factory()->for($post, 'commentable')->create();

    $this->actingAs($author, 'sanctum');

    $this->deleteJson(route('posts.comments.destroy', [$post, $comment]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Comment deleted successfully'))
         );

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
})->with([
    fn() => [asAuthor(), Post::factory()->create()],
]);

it('comment owner can delete their own comment if they have permission', function (User $user, Post $post) {
    $comment = Comment::factory()->for($post, 'commentable')->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $this->deleteJson(route('posts.comments.destroy', [$post, $comment]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Comment deleted successfully'))
         );

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
})->with([
    fn() => [asAuthor(), Post::factory()->create()],
]);

it('fails if comment does not belong to post', function (User $user, Post $post, Post $otherPost) {
    $comment = Comment::factory()->for($otherPost, 'commentable')->create();

    $this->actingAs($user, 'sanctum');

    $this->deleteJson(route('posts.comments.destroy', [$post, $comment]))
         ->assertForbidden();

    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
})->with([
    fn() => [asAdmin(), Post::factory()->create(), Post::factory()->create()],
]);

it('guest cannot delete a comment', function (Post $post) {
    $comment = Comment::factory()->for($post, 'commentable')->create();

    $this->deleteJson(route('posts.comments.destroy', [$post, $comment]))
         ->assertUnauthorized()
         ->assertJson(fn(AssertableJson $json) => $json->where('message', __('Unauthenticated.'))
         );

    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
})->with([
    fn() => Post::factory()->create(),
]);

/**
 * Show Comment
 */
it('can show a comment belonging to a post', function (User $user, Post $post) {
    $this->actingAs($user, 'sanctum');

    $comment = Comment::factory()->for($user, 'author')->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.show', [$post, $comment]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comment retrieved successfully.'))
             ->has('data', fn($json) => $json
                 ->where('id', $comment->id)
                 ->where('body', $comment->body)
                 ->has('author', fn($author) => $author
                     ->where('id', $user->id)
                     ->where('name', $user->name)
                     ->etc()
                 )
                 ->etc()
             )
             ->etc()
         );
})->with([
    fn() => [asAdmin(), Post::factory()->create()],
    fn() => [asAuthor(), Post::factory()->create()],
]);

it('cannot show a comment that does not belong to the post', function (User $user, Post $post) {
    $this->actingAs($user, 'sanctum');

    $otherPost = Post::factory()->create();
    $comment = Comment::factory()->for($user, 'author')->create([
        'commentable_id'   => $otherPost->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.show', [$post, $comment]))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', false)
             ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => [asAdmin(), Post::factory()->create()],
]);

it('guest can view a comment belonging to a post', function (Post $post) {
    $comment = Comment::factory()->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.show', [$post, $comment]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comment retrieved successfully.'))
             ->has('data', fn($json) => $json
                 ->where('id', $comment->id)
                 ->where('body', $comment->body)
                 ->etc()
             )
         );
})->with([
    fn() => Post::factory()->create(),
]);

/**
 * Index Comments
 */
it('guest can list comments for a post', function (Post $post) {
    $comments = Comment::factory()->count(3)->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.index', $post))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comments fetched successfully'))
             ->has('data', $comments->count())
             ->has('meta')
         );
})->with([
    fn() => Post::factory()->create(),
]);

it('paginates comments for a post', function (Post $post) {
    Comment::factory()->count(15)->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.index', $post).'?per_page=5&page=2')
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comments fetched successfully'))
             ->has('data', 5)
             ->has('meta', fn($meta) => $meta
                 ->where('current_page', 2)
                 ->where('per_page', 5)
                 ->etc()
             )
         );
})->with([
    fn() => Post::factory()->create(),
]);

it('filters comments by search term (body and author)', function (Post $post) {
    $user = User::factory()->create(['name' => 'Searchable User']);
    $matchingComment = Comment::factory()->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
        'body'             => 'This contains special keyword',
        'user_id'          => $user->id,
    ]);
    $nonMatchingComment = Comment::factory()->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
        'body'             => 'Other text',
    ]);

    $this->getJson(route('posts.comments.index', $post).'?search=keyword')
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comments fetched successfully'))
             ->has('data', 1, fn($json) => $json
                 ->where('id', $matchingComment->id)
                 ->etc()
             )
             ->has('meta')
         );
})->with([
    fn() => Post::factory()->create(),
]);

it('filters comments by author id', function (Post $post) {
    $author = User::factory()->create();
    $matchingComment = Comment::factory()->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
        'user_id'          => $author->id,
    ]);
    Comment::factory()->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.index', $post).'?author='.$author->id)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comments fetched successfully'))
             ->has('data', 1, fn($json) => $json
                 ->where('id', $matchingComment->id)
                 ->etc()
             )
             ->has('meta')
         );
})->with([
    fn() => Post::factory()->create(),
]);

it('does not return comments from other posts', function (Post $post, Post $otherPost) {
    $postComment = Comment::factory()->create([
        'commentable_id'   => $post->id,
        'commentable_type' => Post::class,
    ]);
    Comment::factory()->create([
        'commentable_id'   => $otherPost->id,
        'commentable_type' => Post::class,
    ]);

    $this->getJson(route('posts.comments.index', $post))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json
             ->where('success', true)
             ->where('message', __('Comments fetched successfully'))
             ->has('data', 1, fn($json) => $json
                 ->where('id', $postComment->id)
                 ->etc()
             )
             ->has('meta')
         );
})->with([
    fn() => [Post::factory()->create(['title' => 'Post 1']), Post::factory()->create(['title' => 'Post 2'])],
]);
