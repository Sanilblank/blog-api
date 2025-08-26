<?php

use App\Enums\Roles;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

uses()->group('user');

/**
 * Index User
 */
it('admin can access user index', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $this->getJson(route('users.index'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Users retrieved successfully.'))
                                                       ->has('data')
                                                       ->has('meta')
         );
})->with([
    fn() => asAdmin(),
]);

it('author cannot access user index', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $this->getJson(route('users.index'))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => asAuthor(),
]);

it('admin can filter users by search term', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    User::factory()->create(['name' => 'Unique Name']);

    $this->getJson(route('users.index', ['search' => 'Unique Name']))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Users retrieved successfully.'))
                                                       ->has('data', 1)
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);

it('admin can filter users by roles', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $author = User::factory()->create();
    $author->assignRole(Roles::AUTHOR->value);

    $adminUser = User::factory()->create();
    $adminUser->assignRole(Roles::ADMIN->value);

    $this->getJson(route('users.index', ['roles' => Roles::AUTHOR->value]))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Users retrieved successfully.'))
                                                       ->has(
                                                           'data',
                                                           fn($json) => $json->where(
                                                               '0.roles.0.name',
                                                               Roles::AUTHOR->value
                                                           )
                                                       )
                                                       ->etc()
         );
})->with([
    fn() => asAdmin(),
]);

/**
 * Create User
 */
it('admin can create new user', function (User $user) {
    $this->actingAs($user, 'sanctum');

    $data = [
        'name'                  => 'Test User',
        'email'                 => 'testuser@gmail.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'role'                  => Roles::AUTHOR->value,
    ];

    $this->postJson(route('users.store'), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User created successfully.'))
                                                       ->has('data')
         );
})->with([
    fn() => asAdmin(),
]);

it('author cannot create new user', function (User $user) {
    $this->actingAs($user, 'sanctum');

    $data = [
        'name'                  => 'Test User',
        'email'                 => 'testuser@gmail.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'role'                  => Roles::AUTHOR->value,
    ];

    $this->postJson(route('users.store'), $data)
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => asAuthor(),
]);

/**
 * Update User
 */
it('admin can update an author', function (User $admin, User $author) {
    $this->actingAs($admin, 'sanctum');

    $data = [
        'name'  => 'Updated Author',
        'email' => 'updated_author@gmail.com',
    ];

    $this->putJson(route('users.update', $author), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User updated successfully.'))
                                                       ->has('data')
         );

    $this->assertDatabaseHas('users', [
        'id'    => $author->id,
        'name'  => 'Updated Author',
        'email' => 'updated_author@gmail.com',
    ]);
})->with([
    fn() => [asAdmin(), asAuthor()],
]);

it('admin cannot update another admin', function (User $admin1, User $admin2) {
    $this->actingAs($admin1, 'sanctum');

    $data = ['name' => 'Hacked Admin', 'email' => 'hacked_admin@gmail.com'];

    $this->putJson(route('users.update', $admin2), $data)
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => [asAdmin(), asAdmin()],
]);

it('author can update their own profile', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $data = ['name' => 'Self Updated Author', 'email' => 'self_updated_author@gmail.com'];

    $this->putJson(route('users.update', $author), $data)
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User updated successfully.'))
                                                       ->has('data')
         );

    $this->assertDatabaseHas('users', [
        'id'   => $author->id,
        'name' => 'Self Updated Author',
    ]);
})->with([
    fn() => asAuthor(),
]);

it('author cannot update another author', function (User $author1, User $author2) {
    $this->actingAs($author1, 'sanctum');

    $data = ['name' => 'Should Not Work', 'email' => 'should_not_work@gmail.com'];

    $this->putJson(route('users.update', $author2), $data)
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => [asAuthor(), asAuthor()],
]);

it('author cannot update an admin', function (User $author, User $admin) {
    $this->actingAs($author, 'sanctum');

    $data = ['name' => 'Malicious Try', 'email' => 'malicious_try@gmail.com'];

    $this->putJson(route('users.update', $admin), $data)
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => [asAuthor(), asAdmin()],
]);

/**
 * Show User
 */
it('admin can view an author', function (User $admin, User $author) {
    $this->actingAs($admin, 'sanctum');

    $this->getJson(route('users.show', $author))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User retrieved successfully.'))
                                                       ->has('data')
         );
})->with([
    fn() => [asAdmin(), asAuthor()],
]);

it('admin can view another admin', function (User $admin1, User $admin2) {
    $this->actingAs($admin1, 'sanctum');

    $this->getJson(route('users.show', $admin2))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User retrieved successfully.'))
                                                       ->has('data')
         );
})->with([
    fn() => [asAdmin(), asAdmin()],
]);

it('author can view their own profile', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $this->getJson(route('users.show', $author))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User retrieved successfully.'))
                                                       ->has('data')
         );
})->with([
    fn() => asAuthor(),
]);

it('author cannot view another author', function (User $author1, User $author2) {
    $this->actingAs($author1, 'sanctum');

    $this->getJson(route('users.show', $author2))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => [asAuthor(), asAuthor()],
]);

it('author cannot view an admin', function (User $author, User $admin) {
    $this->actingAs($author, 'sanctum');

    $this->getJson(route('users.show', $admin))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );
})->with([
    fn() => [asAuthor(), asAdmin()],
]);

/**
 * Delete User
 */
it('admin can delete an author', function (User $admin, User $author) {
    $this->actingAs($admin, 'sanctum');

    $this->deleteJson(route('users.destroy', $author))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User deleted successfully.'))
         );

    $this->assertDatabaseMissing('users', [
        'id' => $author->id,
    ]);
})->with([
    fn() => [asAdmin(), asAuthor()],
]);


it('admin cannot delete another admin', function (User $admin1, User $admin2) {
    $this->actingAs($admin1, 'sanctum');

    $this->deleteJson(route('users.destroy', $admin2))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('users', [
        'id' => $admin2->id,
    ]);
})->with([
    fn() => [asAdmin(), asAdmin()],
]);

it('author can delete their own account', function (User $author) {
    $this->actingAs($author, 'sanctum');

    $this->deleteJson(route('users.destroy', $author))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User deleted successfully.'))
         );

    $this->assertDatabaseMissing('users', [
        'id' => $author->id,
    ]);
})->with([
    fn() => asAuthor(),
]);

it('admin can delete their own account', function (User $admin) {
    $this->actingAs($admin, 'sanctum');

    $this->deleteJson(route('users.destroy', $admin))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('User deleted successfully.'))
         );

    $this->assertDatabaseMissing('users', [
        'id' => $admin->id,
    ]);
})->with([
    fn() => asAdmin(),
]);

it('author cannot delete another author', function (User $author1, User $author2) {
    $this->actingAs($author1, 'sanctum');

    $this->deleteJson(route('users.destroy', $author2))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('users', [
        'id' => $author2->id,
    ]);
})->with([
    fn() => [asAuthor(), asAuthor()],
]);

it('author cannot delete an admin', function (User $author, User $admin) {
    $this->actingAs($author, 'sanctum');

    $this->deleteJson(route('users.destroy', $admin))
         ->assertForbidden()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', false)
                                                       ->where('message', __('This action is unauthorized.'))
         );

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
})->with([
    fn() => [asAuthor(), asAdmin()],
]);
