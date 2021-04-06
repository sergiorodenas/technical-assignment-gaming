<?php

namespace Tests\Feature;

use App\Models\Score;
use App\Models\User;
use App\Models\Weapon;
use Tests\TestCase;
use App\Models\Game;
use App\Models\HighScore;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ScoresControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_returns_only_the_high_scores_per_weapon(){
        $user = User::factory()->create();
        $weapon = Weapon::factory()->create();

        Score::factory()->for($user)->for($weapon)->state([
            'score' => 100
        ])->create();

        Score::factory()->for($user)->for($weapon)->state([
            'score' => 1000
        ])->create();

        $this->get(route('scores.index'))->assertJsonFragment(['high_score' => 1000])->assertJsonMissing(['high_score' => 100]);
    }

    public function test_it_returns_the_high_scores_sorted(){
        $user_low_score = User::factory()->create();
        $user_high_score = User::factory()->create();

        Score::factory()->for($user_low_score)->state([
            'score' => 100
        ])->create();

        Score::factory()->for($user_high_score)->state([
            'score' => 1000
        ])->create();

        $this->get(route('scores.index'))->assertSeeInOrder([1000, 100]);
    }

    public function test_it_can_filter_by_weapon(){
        $user = User::factory()->create();
        $weapon = Weapon::factory()->create();
        $weapon_discarded = Weapon::factory()->create();

        Score::factory()->for($user)->for($weapon)->state([
            'score' => 100
        ])->create();

        Score::factory()->for($user)->for($weapon_discarded)->state([
            'score' => 1000
        ])->create();

        // Sets the user global high score to another value
        Score::factory()->for($user)->state([
            'score' => 3333
        ])->create();

        $this->get(route('scores.index', ['weapon' => $weapon]))
            ->assertJsonFragment(['high_score' => 100])->assertJsonMissing(['high_score' => 1000]);
    }

    public function test_it_can_filter_by_game(){
        $user = User::factory()->create();

        $game = Game::factory()->create();
        $game_discarded = Game::factory()->create();
        $weapon = Weapon::factory()->for($game)->create();
        $weapon_discarded = Weapon::factory()->for($game_discarded)->create();

        Score::factory()->for($user)->for($weapon)->state([
            'score' => 100
        ])->create();

        Score::factory()->for($user)->for($weapon_discarded)->state([
            'score' => 1000
        ])->create();

        // Sets the user global high score to another value
        Score::factory()->for($user)->state([
            'score' => 3333
        ])->create();

        $this->get(route('scores.index', ['weapon' => $weapon]))
            ->assertJsonFragment(['high_score' => 100])->assertJsonMissing(['high_score' => 1000]);
    }

    public function test_it_cannot_filter_by_game_and_weapon_at_the_same_time(){
        $weapon = Weapon::factory()->create();
        $game = Game::factory()->create();

        $this->get(route('scores.index', ['weapon' => $weapon, 'game' => $game]))
            ->assertStatus(400)->assertSee('weapon and game filters cannot be applied at the same time');
    }

    public function test_it_cannot_filter_by_unexisting_weapons(){
        $this->get(route('scores.index', ['weapon' => 1]))
            ->assertStatus(400);
    }

    public function test_it_cannot_filter_by_strange_filter_values(){
        $this->get(route('scores.index', ['weapon' => 'null']))
            ->assertStatus(400);

        $this->get(route('scores.index', ['weapon' => '']))
            ->assertStatus(400);

        $this->get(route('scores.index', ['weapon' => '? OR 1=1']))
            ->assertStatus(400);
    }

    public function it_returns_a_valid_response_with_no_high_scores(){
        $this->get(route('scores.index'))->assertStatus(200);
    }

    public function it_returns_pagination_metadata(){
        $this->get(route('scores.index'))->assertSeeInOrder(['current_page', 'data', 'first_page_url', 'from', 'last_page', 'last_page_url']);
    }

    // There is no store method, but wanted to test the sync anyway
    public function it_updates_high_scores_when_scores_are_created(){
        $user = User::factory()->create();
        $weapon = Weapon::factory()->create();

        Score::factory()->for($user)->for($weapon)->state(['score' => 100])->create();
        Score::factory()->for($user)->for($weapon)->state(['score' => 1000])->create();

        $this->assertCount(1, HighScore::count());
        $this->assertEquals(1000, HighScore::first()->high_score);
    }
}
