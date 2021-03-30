<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition()
    {
        return [
            'name' => $name = $this->faker->words(3, true),
            'short_name' => $this->faker->word,
            'slug' => Str::slug($name, '_')
        ];
    }
}
