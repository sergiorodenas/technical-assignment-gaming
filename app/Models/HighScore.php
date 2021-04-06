<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class HighScore extends Pivot
{
    public $incrementing = true;
    
    protected $table = 'user_weapon_high_scores';

    public function user(){
        return $this->belongsTo(User::class);
    }
}
