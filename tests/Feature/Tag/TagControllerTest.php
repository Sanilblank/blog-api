<?php

use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

uses()->group('tag');

/**
 * Store Tag
 */
it('admin can create a new tag', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $data = [
        'name' => 'Game',
    ];

    $this->postJson(route('tags.store'), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tag created successfully'))
         );

    $this->assertDatabaseHas('tags', [
        'name' => 'Game',
        'slug' => Str::slug('Game'),
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot create a tag', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $data = [
        'name' => 'Unauthorized Tag',
    ];

    $this->postJson(route('tags.store'), $data)
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseMissing('tags', [
        'name' => 'Unauthorized Tag',
    ]);
})->with([
    fn() => asAuthor(),
]);

it('admin cannot create a tag with a duplicate name (case insensitive)', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    Tag::factory()->create(['name' => 'Sports']);

    $this->postJson(route('tags.store'), ['name' => 'sports'])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.unique', ['attribute' => 'name']))
                                              ->has('errors.name')
         );

    $this->assertDatabaseCount('tags', 1);
})->with([
    fn() => asAdmin(),
]);

it('fails validation if name is missing', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $this->postJson(route('tags.store'), [])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.required', ['attribute' => 'name']))
                                              ->has('errors.name')
         );
})->with([
    fn() => asAdmin(),
]);

/**
 * Update Tag
 */
it('admin can update a tag', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $tag = Tag::factory()->create([
        'name' => 'Old Name',
        'slug' => Str::slug('Old Name'),
    ]);

    $data = [
        'name' => 'Updated Name',
    ];

    $this->putJson(route('tags.update', $tag->id), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tag updated successfully'))
         );

    $this->assertDatabaseHas('tags', [
        'id'   => $tag->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot update a tag', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $tag = Tag::factory()->create(['name' => 'Original']);

    $this->putJson(route('tags.update', $tag->id), [
        'name' => 'Attempted Update',
    ])
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('tags', [
        'id'   => $tag->id,
        'name' => 'Original', // unchanged
    ]);
})->with([
    fn() => asAuthor(),
]);

it('admin cannot update a tag with a duplicate name (case insensitive)', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    Tag::factory()->create(['name' => 'Sports']);
    $target = Tag::factory()->create(['name' => 'News']);

    $this->putJson(route('tags.update', $target->id), [
        'name' => 'sports',
    ])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.unique', ['attribute' => 'name']))
                                              ->has('errors.name')
         );

    $this->assertDatabaseHas('tags', [
        'id'   => $target->id,
        'name' => 'News',
    ]);
})->with([
    fn() => asAdmin(),
]);

it('fails validation if name is missing on update', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $tag = Tag::factory()->create(['name' => 'Original']);

    $this->putJson(route('tags.update', $tag->id), [])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.required', ['attribute' => 'name']))
                                              ->has('errors.name')
         );

    $this->assertDatabaseHas('tags', [
        'id'   => $tag->id,
        'name' => 'Original',
    ]);
})->with([
    fn() => asAdmin(),
]);

/**
 * Delete Tag
 */
it('admin can delete a tag', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $tag = Tag::factory()->create();

    $this->deleteJson(route('tags.destroy', $tag->id))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tag deleted successfully'))
         );

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot delete a tag', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $tag = Tag::factory()->create();

    $this->deleteJson(route('tags.destroy', $tag->id))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
    ]);
})->with([
    fn() => asAuthor(),
]);

it('cannot delete a non-existent tag', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $this->deleteJson(route('tags.destroy', 999999))
         ->assertNotFound();
})->with([
    fn() => asAdmin(),
]);

/**
 * Show Tag
 */
it('can view a tag', function () {
    $tag = Tag::factory()->create([
        'name' => 'Sports',
        'slug' => 'sports',
    ]);

    $this->getJson(route('tags.show', $tag->id))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tag details.'))
                                                       ->has(
                                                           'data',
                                                           fn(AssertableJson $json) => $json->where('id', $tag->id)
                                                                                            ->where('name', 'Sports')
                                                                                            ->where('slug', 'sports')
                                                                                            ->etc()
                                                       )
         );
});

it('returns 404 if tag does not exist', function () {
    $this->getJson(route('tags.show', 999999))
         ->assertNotFound();
});

/**
 * Index Tags
 */
it('returns a paginated list of tags', function () {
    Tag::factory()->count(5)->create();

    $this->getJson(route('tags.index'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tags fetched successfully'))
                                                       ->has('data', 5)
                                                       ->has('meta')
                                                       ->etc()
         );
});

it('filters tags by search term', function () {
    Tag::factory()->create(['name' => 'Sports', 'slug' => 'sports']);
    Tag::factory()->create(['name' => 'Music', 'slug' => 'music']);

    $this->getJson(route('tags.index', ['search' => 'Sport']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tags fetched successfully'))
                                                       ->has(
                                                           'data',
                                                           1,
                                                           fn(AssertableJson $json) => $json->where('name', 'Sports')
                                                                                            ->where('slug', 'sports')
                                                                                            ->etc()
                                                       )
                                                       ->has('meta')
         );
});

it('returns empty data if no tags exist', function () {
    $this->getJson(route('tags.index'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Tags fetched successfully'))
                                                       ->has('data', 0)
                                                       ->has('meta')
         );
});
