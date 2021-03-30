<?php

namespace Database\Factories;

use App\Models\Score;
use App\Models\User;
use App\Models\Weapon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScoreFactory extends Factory
{
    protected $model = Score::class;

    public function definition(): Array
    {
        return [
            'user_id' => User::factory(),
            'weapon_id' => Weapon::factory(),
            'score' => $this->faker->numberBetween(100, 2147483647)
        ];
    }
}
