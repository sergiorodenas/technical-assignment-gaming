<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use App\Models\HighScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScoresController extends Controller
{
    public function index(Request $request){
        $validation = Validator::make($request->all(), [
            'weapon' => ['integer', 'exists:weapons,id'],
            'game' => ['integer', 'exists:games,id'],
        ]);

        if($validation->fails()){
            return response()->json($validation->getMessageBag()->getMessages(), 400);
        }

        abort_if($request->has('weapon') && $request->has('game'), 400, 'weapon and game filters cannot be applied at the same time');
        
        $usersRankAndPercentileQuery = DB::table('users')
            ->selectRaw('id, RANK() OVER (ORDER BY high_score DESC) as user_global_rank, CEILING(PERCENT_RANK() OVER (ORDER BY high_score DESC) * 100) as user_global_percentile')
            ->groupBy('id'); // So we can keep mysql connection in strict mode

        return HighScore::with('user')->where(function($query) use (&$request){
            if($request->has('weapon')){
                $query->where('weapon_id', $request->weapon);
            }
        })->where(function($query) use (&$request){
            if($request->has('game')){
                $query->whereIn('weapon_id', Weapon::whereHas('game', function($query_game) use (&$request){
                    $query_game->where('id', $request->game);
                })->pluck('id'));
            }
        })->leftJoinSub($usersRankAndPercentileQuery, 'user_rank_and_percent', function($join){
            $join->on('user_weapon_high_scores.user_id', '=', 'user_rank_and_percent.id');
        })->orderBy('high_score', 'desc')->paginate();
    }
}
