<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Inspiring;
use Workbench\App\Models\Post;

/**
 * @template TModel of \Workbench\App\Models\Post
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(4, asText: true),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($post) {
            $quote = Inspiring::quotes()->random();

            $post->update(['body' => <<<HTML
            <div>Hello <b><i>World</i></b></div>
            <div></div>
            <div>Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusantium delectus, culpa ipsum laborum quibusdam et architecto asperiores, impedit alias eveniet enim temporibus totam. Officiis, provident perspiciatis facere amet unde enim.</div>
            <div></div>
            <blockquote>{$quote}</blockquote>
            <h1>A title</h1>
            <ul>
                <li>First item</li>
                <li>Second item</li>
            </ul>
            HTML]);
        });
    }
}
