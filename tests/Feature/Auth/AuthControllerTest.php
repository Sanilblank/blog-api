<?php

use App\Enums\Roles;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

uses()->group('auth');

/**
 * Login
 */
it('fails to login with invalid credentials', function () {
    $response = $this->postJson(route('login'), [
        'email'    => 'wrong@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
             ->assertJson(fn(AssertableJson $json) => $json->where('message', __('Invalid credentials'))->has('errors')
             );
});

it('can login with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'admin@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
             ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                           ->where('message', __('Logged in successfully!'))
                                                           ->has('data.user')
                                                           ->has('data.token')
             );

    expect($response['data']['token'])->toBeString();
});

/**
 * Logout
 */
it('cannot logout when unauthenticated', function () {
    $this->postJson(route('logout'))
         ->assertUnauthorized()
         ->assertJson(fn(AssertableJson $json) => $json->where('message', __('Unauthenticated.'))
         );
});

it('can logout successfully when authenticated', function () {
    $user = User::factory()->create([
        'email'    => 'admin@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
         ->postJson(route('logout'))
         ->assertOk()
         ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                       ->where('message', __('Logged out successfully!'))
         );
});

/**
 * Register
 */
it('fails to register with invalid data', function () {
    $this->postJson(route('register'), [
        'email'    => 'not-an-email',
        'password' => '123',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
         ->assertJson(function (AssertableJson $json) {
             $json->has('message')
                  ->has('errors');
         });
});

it('registers a new author successfully', function () {
    $response = $this->postJson(route('register'), [
        'name'                  => 'Test Author',
        'email'                 => 'author@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertOk()
             ->assertJson(fn(AssertableJson $json) => $json->where('success', true)
                                                           ->where('message', __('Author registered successfully!'))
                                                           ->has('data.user')
                                                           ->has('data.token')
             );

    $user = User::where('email', 'author@example.com')->first();

    expect($user)->not->toBeNull()
                      ->and($user->roles()->first()->name)->toBe(Roles::AUTHOR->value);
});

it('ignores any role passed and always assigns author role', function () {
    $response = $this->postJson(route('register'), [
        'name'                  => 'Fake Admin',
        'email'                 => 'fakeadmin@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
        'role'                  => Roles::ADMIN->value,
    ]);

    $response->assertOk();

    $user = User::where('email', 'fakeadmin@example.com')->first();

    expect($user->roles()->first()->name)->toBe(Roles::AUTHOR->value);
});
