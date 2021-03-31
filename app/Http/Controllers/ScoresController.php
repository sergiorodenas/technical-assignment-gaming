<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use App\Models\HighScore;
use Illuminate\Http\Request;
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
        
        return HighScore::where(function($query) use (&$request){
            if($request->has('weapon')){
                $query->where('weapon_id', $request->weapon);
            }
        })->where(function($query) use (&$request){
            if($request->has('game')){
                $query->whereIn('weapon_id', Weapon::whereHas('game', function($query_game) use (&$request){
                    $query_game->where('id', $request->game);
                })->pluck('id'));
            }
        })->orderBy('high_score', 'desc')->paginate();
    }
}
