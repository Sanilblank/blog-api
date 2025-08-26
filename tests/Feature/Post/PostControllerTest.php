<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

uses()->group('post');

/**
 * Create Post
 */
it('admin can create a post', function (User $user) {
    $this->actingAs($user, 'sanctum');

    $this->postJson(route('posts.store'), getPostData())
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Post created successfully'))
                                                       ->has('data')
         );

    $this->assertDatabaseHas('posts', [
        'title'   => 'My First Post',
        'body'    => 'This is the body of the post',
        'user_id' => $user->id,
    ]);

    $post = Post::where('title', 'My First Post')->first();
    expect($post->tags)->toHaveCount(3);
})->with([
    fn() => asAdmin(),
    fn() => asAuthor(),
]);

it('guest cannot create a post', function () {
    $this->postJson(route('posts.store'), getPostData())
         ->assertUnauthorized()
         ->assertJson(fn(AssertableJson $json) => $json->where('message', __('Unauthenticated.'))
         );

    $this->assertDatabaseMissing('posts', [
        'title' => 'My First Post',
    ]);
});

/**
 * Update Post
 */
it('admin can update any post', function (User $admin, Post $post) {
    $this->actingAs($admin, 'sanctum');

    $updateData = getPostUpdateData();

    $this->putJson(route('posts.update', $post), $updateData)
         ->assertOk()
         ->assertJson(fn($json) => $json->where('success', true)
                                        ->where('message', __('Post updated successfully'))
                                        ->has('data')
         );

    $this->assertDatabaseHas('posts', [
        'id'    => $post->id,
        'title' => 'Updated Post Title',
        'body'  => 'Updated post body content',
    ]);

    $post->refresh();
    expect($post->tags)->toHaveCount(2);
})->with([
    fn() => [asAdmin(), Post::factory()->create()],
]);

it('author can update own post', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $post = Post::factory()->byUser($author)->withTags()->create();

    $updateData = getPostUpdateData();

    $this->putJson(route('posts.update', $post), $updateData)
         ->assertOk()
         ->assertJson(fn($json) => $json->where('success', true)
                                        ->where('message', __('Post updated successfully'))
                                        ->has('data')
         );

    $post->refresh();
    expect($post->title)->toBe('Updated Post Title')
                        ->and($post->tags)->toHaveCount(2);
})->with([
    fn() => asAuthor(),
]);

it('author cannot update another authors post', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $otherPost = Post::factory()->withTags()->create();

    $updateData = getPostUpdateData();

    $this->putJson(route('posts.update', $otherPost), $updateData)
         ->assertForbidden()
         ->assertJson(fn($json) => $json->where('success', false)->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseMissing('posts', [
        'id'    => $otherPost->id,
        'title' => 'Updated Post Title',
    ]);
})->with([
    fn() => asAuthor(),
]);

it('guest cannot update a post', function () {
    $post = Post::factory()->withTags()->create();

    $updateData = getPostUpdateData();

    $this->putJson(route('posts.update', $post), $updateData)
         ->assertUnauthorized()
         ->assertJson(fn($json) => $json->where('message', __('Unauthenticated.')));
});

/**
 * Delete Post
 */
it('admin can delete any post', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $post = Post::factory()->withTags()->create();

    $this->deleteJson(route('posts.destroy', $post))
         ->assertOk()
         ->assertJson(fn($json) => $json->where('success', true)
                                        ->where('message', __('Post deleted successfully'))
         );

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
})->with([
    fn() => asAdmin(),
]);

it('author can delete own post', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $post = Post::factory()->byUser($author)->withTags()->create();

    $this->deleteJson(route('posts.destroy', $post))
         ->assertOk()
         ->assertJson(fn($json) => $json->where('success', true)
                                        ->where('message', __('Post deleted successfully'))
         );

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
})->with([
    fn() => asAuthor(),
]);

it('author cannot delete another authors post', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $otherPost = Post::factory()->withTags()->create();

    $this->deleteJson(route('posts.destroy', $otherPost))
         ->assertForbidden()
         ->assertJson(fn($json) => $json->where('success', false)->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('posts', ['id' => $otherPost->id]);
})->with([
    fn() => asAuthor(),
]);

it('guest cannot delete a post', function () {
    $post = Post::factory()->withTags()->create();

    $this->deleteJson(route('posts.destroy', $post))
         ->assertUnauthorized()
         ->assertJson(fn($json) => $json->where('message', __('Unauthenticated.')));

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

/**
 * Show Post
 */
it('admin can view a post', function (User $user, Post $post) {
    $this->actingAs($user, 'sanctum');

    $this->getJson(route('posts.show', $post))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Post retrieved successfully.'))
                                                       ->has('data.id')
                                                       ->has('data.title')
                                                       ->has('data.body')
                                                       ->has('data.author')
                                                       ->has('data.category')
                                                       ->has('data.tags')
         );
})->with([
    fn() => [asAdmin(), Post::factory()->withTags()->create()],
]);

it('author can view a post', function (User $user, Post $post) {
    $this->actingAs($user, 'sanctum');

    $this->getJson(route('posts.show', $post))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Post retrieved successfully.'))
                                                       ->has('data.id')
                                                       ->has('data.title')
                                                       ->has('data.body')
                                                       ->has('data.author')
                                                       ->has('data.category')
                                                       ->has('data.tags')
         );
})->with([
    fn() => [asAuthor(), Post::factory()->withTags()->create()],
]);

it('guest can view a post', function (Post $post) {
    $this->getJson(route('posts.show', $post))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Post retrieved successfully.'))
                                                       ->has('data')
         );
})->with([
    fn() => Post::factory()->withTags()->create(),
]);

it('returns 404 if post does not exist', function () {
    $this->getJson(route('posts.show', 99999))
         ->assertNotFound();
});

/**
 * Index Post
 */
it('admin can access post index', function (User $user) {
    $this->actingAs($user, 'sanctum');

    $this->getJson(route('posts.index'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Posts fetched successfully'))
                                                       ->has('data')
                                                       ->has('meta')
         );
})->with([
    fn() => asAdmin(),
    fn() => asAuthor()
]);

it('admin can filter posts by search term', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $author = User::factory()->create(['name' => 'Unique Author']);
    $category = Category::factory()->create(['name' => 'Unique Category']);
    $tag = Tag::factory()->create(['name' => 'UniqueTag']);

    $post = Post::factory()->create([
        'title'       => 'Unique Post Title',
        'category_id' => $category->id,
        'user_id'     => $author->id,
    ]);
    $post->tags()->attach($tag->id);

    $this->getJson(route('posts.index', ['search' => 'Unique']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Posts fetched successfully'))
                                                       ->has('data', 1)
                                                       ->where('data.0.title', 'Unique Post Title')
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);

it('admin can filter posts by category', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Post::factory()->create(['category_id' => $category1->id]);
    Post::factory()->create(['category_id' => $category2->id]);

    $this->getJson(route('posts.index', ['category' => $category1->id]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Posts fetched successfully'))
                                                       ->has('data', 1)
                                                       ->has('meta')
         );
})->with([
    fn() => asAdmin(),
]);

it('admin can filter posts by tags', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $post1 = Post::factory()->create();
    $post1->tags()->attach($tag1->id);

    $post2 = Post::factory()->create();
    $post2->tags()->attach($tag2->id);

    $this->getJson(route('posts.index', ['tags' => $tag1->id]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Posts fetched successfully'))
                                                       ->has('data', 1)
                                                       ->where('data.0.id', $post1->id)
                                                       ->has('meta')
         );
})->with([
    fn() => asAdmin(),
]);

it('can search posts by title', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $post = Post::factory()->create(['title' => 'Unique Title']);

    $this->getJson(route('posts.index', ['search' => 'Unique Title']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('data.0.title', 'Unique Title')
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);

it('can search posts by author name', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $author = User::factory()->create(['name' => 'Special Author']);
    Post::factory()->create(['user_id' => $author->id]);

    $this->getJson(route('posts.index', ['search' => 'Special Author']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('data.0.author.id', $author->id)
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);

it('can search posts by category name', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $category = Category::factory()->create(['name' => 'Special Category']);
    Post::factory()->create(['category_id' => $category->id]);

    $this->getJson(route('posts.index', ['search' => 'Special Category']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('data.0.category.id', $category->id)
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);

it('can search posts by tag name', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $tag = Tag::factory()->create(['name' => 'SpecialTag']);
    $post = Post::factory()->create();
    $post->tags()->attach($tag->id);

    $this->getJson(route('posts.index', ['search' => 'SpecialTag']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('data.0.id', $post->id)
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);
