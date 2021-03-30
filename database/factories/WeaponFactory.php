<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Weapon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WeaponFactory extends Factory
{
    protected $model = Weapon::class;

    public function definition()
    {
        return [
            'game_id' => Game::factory(),
            'name' => $name = $this->faker->words(3, true),
            'slug' => Str::slug($name, '_')
        ];
    }
}
