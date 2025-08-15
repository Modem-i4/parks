<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6, true),
            'body' => $this->generateFakeHtmlBody(),
            'author_id' => User::factory(),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
    protected function generateFakeHtmlBody(): string
    {
        $paragraphs = $this->faker->paragraphs(rand(3, 6));
        return collect($paragraphs)->map(fn($p) => "<p>{$p}</p>")->implode("\n");
    }
}
