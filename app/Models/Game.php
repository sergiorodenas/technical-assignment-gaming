<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    public function weapons(){
        return $this->hasMany(Weapon::class);
    }
}
