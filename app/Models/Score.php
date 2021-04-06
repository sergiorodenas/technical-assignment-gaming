<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected static function booted(){
        static::created(function (Score $score) {  
            $newScore = $score->score;

            $weaponOldScore = optional($score->user->high_scores()->where('weapon_id', $score->weapon_id)->first())->high_score ?? 0;
            if($newScore > $weaponOldScore){
                $score->user->high_scores()->syncWithoutDetaching([$score->weapon_id => ['high_score' => $newScore]]);
            }

            $userOldScore = $score->user->high_score ?? 0;
            if($newScore > $userOldScore){
                $score->user->update(['high_score' => $newScore]);
            }
        });
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function weapon(){
        return $this->belongsTo(Weapon::class);
    }
}
