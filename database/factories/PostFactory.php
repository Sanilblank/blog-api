<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(6),
            'body'        => fake()->paragraph(2),
            'category_id' => Category::factory(),
            'user_id'     => User::factory(),
        ];
    }

    /**
     * @param int $count The number of tags to associate with the post.
     *
     * @return static
     */
    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            $tags = Tag::factory()->count($count)->create();
            $post->tags()->sync($tags->pluck('id')->toArray());
        });
    }

    /**
     * State for posts with specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
