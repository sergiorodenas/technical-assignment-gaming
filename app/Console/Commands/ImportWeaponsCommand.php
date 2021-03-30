<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;
use Rodenastyle\StreamParser\StreamParser;
use Tightenco\Collect\Support\Collection;

class ImportWeaponsCommand extends Command
{
    protected $signature = 'weapon:load';
    protected $description = 'Imports weapons from weapons.json file to database';

    public function handle(): Int
    {
        // Happy to be able to use my own known package here
        StreamParser::json(base_path('games.json'))->each(function(Collection $file_game){
            $game = Game::firstOrCreate([
                'slug' => $file_game->get('id')
            ], [
                'name' => $file_game->get('name'),
                'short_name' => $file_game->get('short_name')
            ]);

            // We won't use this command that much, let's make a sync here even loosing performance
            $file_game->get('weapons')->each(function(Collection $file_weapon) use (&$game){
                $game->weapons()->firstOrCreate([
                    'slug' => $file_weapon->get('id')
                ], [
                    'name' => $file_weapon->get('name')
                ]);
            });
        });

        return 0;
    }
}
