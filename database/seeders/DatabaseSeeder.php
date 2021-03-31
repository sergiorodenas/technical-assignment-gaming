<?php

namespace Database\Seeders;

use App\Models\Score;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Weapon;

class DatabaseSeeder extends Seeder
{
    public function run(): void // I highly prefer to type rather than DocBlocks
    {
        $users = collect()->times(10)->map(function(){
            return User::factory()->create();
        });
        
        $weapons = collect()->times(10)->map(function(){
            return Weapon::factory()->create();
        });

        // This takes around 15 minutes (10M rows, 1GB)
        collect()->times(1000)->each(function() use (&$users, &$weapons){ // I like to be explicit with references 
            $scores = Score::factory(10000)->state([
                'user_id' => function() use (&$users){
                    return $users->random(1)->first();
                },

                'weapon_id' => function() use (&$weapons){
                    return $weapons->random(1)->first();
                },
            ])->make();

            // This wont fire Score::created event we're listening at the Score model class
            Score::insert($scores->toArray());
        });

        /**
         * I thought about using Redis for high scores but I've taken it as a MySQL challenge. 
         * I wanted it to be simple and readable anyway so I've deformalized and grouped the maximum values from scores table on user_weapon_high_scores table.
         */

        // Generate high scores grouping by two columns (takes around 3sec on my laptop)
        Score::selectRaw('MAX(score) as high_score, weapon_id, user_id')->groupBy('user_id', 'weapon_id')->get()->groupBy('user_id')->each(function($scores, $user_id){
            $formattedScores = $scores->mapWithKeys(function($score){
                return [$score->weapon_id => ['high_score' => $score->high_score]];
            })->toArray();
            
            User::find($user_id)->high_scores()->sync($formattedScores);
        });
    }
}
