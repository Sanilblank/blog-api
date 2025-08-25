<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Enums\Roles;
use App\Models\User;

pest()->extend(Tests\TestCase::class)
      ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
      ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Creates a new user with the admin role and logs them in.
 *
 * @return User
 */
function asAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(Roles::ADMIN);

    return $user;
}

/**
 * Creates a new user with the author role and logs them in.
 *
 * @return User
 */
function asAuthor(): User
{
    $user = User::factory()->create();
    $user->assignRole(Roles::AUTHOR);

    return $user;
}
