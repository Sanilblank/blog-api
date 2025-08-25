<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Filters\UserFilter;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * @var string
     */
    protected $guard_name = 'sanctum';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable
        = [
            'name',
            'email',
            'password',
            'email_verified_at',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden
        = [
            'password',
            'remember_token',
        ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Filter the users using the given filter.
     *
     * @param $query
     * @param $filter
     *
     * @return void
     */
    public function scopeFilter($query, $filter): void
    {
        tap(new UserFilter($query, $filter), function ($userFilter) {
            return $userFilter->apply();
        });
    }
}
