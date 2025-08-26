<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

uses()->group('category');

/**
 * Store Category
 */
it('admin can create a new category', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $data = [
        'name' => 'Tech News',
    ];

    $this->postJson(route('categories.store'), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Category created successfully'))
         );

    $this->assertDatabaseHas('categories', [
        'name' => 'Tech News',
        'slug' => Str::slug('Tech News'),
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot create a category', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $data = [
        'name' => 'Unauthorized Category',
    ];

    $this->postJson(route('categories.store'), $data)
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseMissing('categories', [
        'name' => 'Unauthorized Category',
    ]);
})->with([
    fn() => asAuthor(),
]);

it('admin cannot create a category with a duplicate name (case insensitive)', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    Category::factory()->create(['name' => 'Sports']);

    $this->postJson(route('categories.store'), ['name' => 'sports'])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.unique', ['attribute' => 'name']))
                                              ->has('errors.name')
         );

    $this->assertDatabaseCount('categories', 1);
})->with([
    fn() => asAdmin(),
]);

it('fails validation if name is missing', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $this->postJson(route('categories.store'), [])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.required', ['attribute' => 'name']))
                                              ->has('errors.name')
         );
})->with([
    fn() => asAdmin(),
]);

/**
 * Update Category
 */
it('admin can update a category', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => Str::slug('Old Name'),
    ]);

    $data = [
        'name' => 'Updated Name',
    ];

    $this->putJson(route('categories.update', $category->id), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Category updated successfully'))
         );

    $this->assertDatabaseHas('categories', [
        'id'   => $category->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot update a category', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $category = Category::factory()->create(['name' => 'Original']);

    $this->putJson(route('categories.update', $category->id), [
        'name' => 'Attempted Update',
    ])
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('categories', [
        'id'   => $category->id,
        'name' => 'Original', // unchanged
    ]);
})->with([
    fn() => asAuthor(),
]);

it('admin cannot update a category with a duplicate name (case insensitive)', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    Category::factory()->create(['name' => 'Sports']);
    $target = Category::factory()->create(['name' => 'News']);

    $this->putJson(route('categories.update', $target->id), [
        'name' => 'sports',
    ])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.unique', ['attribute' => 'name']))
                                              ->has('errors.name')
         );

    $this->assertDatabaseHas('categories', [
        'id'   => $target->id,
        'name' => 'News',
    ]);
})->with([
    fn() => asAdmin(),
]);

it('fails validation if name is missing on update', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $category = Category::factory()->create(['name' => 'Original']);

    $this->putJson(route('categories.update', $category->id), [])
         ->assertUnprocessable()
         ->assertJson(
             fn(AssertableJson $json) => $json->where('message', __('validation.required', ['attribute' => 'name']))
                                              ->has('errors.name')
         );

    $this->assertDatabaseHas('categories', [
        'id'   => $category->id,
        'name' => 'Original',
    ]);
})->with([
    fn() => asAdmin(),
]);

/**
 * Delete Category
 */
it('admin can delete a category', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $category = Category::factory()->create();

    $this->deleteJson(route('categories.destroy', $category->id))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Category deleted successfully'))
         );

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot delete a category', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $category = Category::factory()->create();

    $this->deleteJson(route('categories.destroy', $category->id))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
    ]);
})->with([
    fn() => asAuthor(),
]);

it('cannot delete a non-existent category', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $this->deleteJson(route('categories.destroy', 999999))
         ->assertNotFound();
})->with([
    fn() => asAdmin(),
]);

/**
 * Show Category
 */
it('can view a category', function () {
    $category = Category::factory()->create([
        'name' => 'Sports',
        'slug' => 'sports',
    ]);

    $this->getJson(route('categories.show', $category->id))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Category details.'))
                                                       ->has(
                                                           'data',
                                                           fn(AssertableJson $json) => $json->where('id', $category->id)
                                                                                            ->where('name', 'Sports')
                                                                                            ->where('slug', 'sports')
                                                                                            ->etc()
                                                       )
         );
});

it('returns 404 if category does not exist', function () {
    $this->getJson(route('categories.show', 999999))
         ->assertNotFound();
});

/**
 * Index Categories
 */
it('returns a paginated list of categories', function () {
    Category::factory()->count(5)->create();

    $this->getJson(route('categories.index'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Categories fetched successfully'))
                                                       ->has('data', 5)
                                                       ->has('meta')
                                                       ->etc()
         );
});

it('filters categories by search term', function () {
    Category::factory()->create(['name' => 'Sports', 'slug' => 'sports']);
    Category::factory()->create(['name' => 'Music', 'slug' => 'music']);

    $this->getJson(route('categories.index', ['search' => 'Sport']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Categories fetched successfully'))
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

it('returns empty data if no categories exist', function () {
    $this->getJson(route('categories.index'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Categories fetched successfully'))
                                                       ->has('data', 0)
                                                       ->has('meta')
         );
});
