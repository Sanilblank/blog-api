<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body'             => $this->faker->paragraph,
            'user_id'          => User::factory(),
            'commentable_id'   => Post::factory(),
            'commentable_type' => Post::class,
        ];
    }

    /**
     * Assign a specific post to the comment.
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn() => [
            'commentable_id'   => $post->id,
            'commentable_type' => Post::class,
        ]);
    }

    /**
     * Assign a specific user as author of the comment.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn() => [
            'user_id' => $user->id,
        ]);
    }
}
