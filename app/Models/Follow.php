<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    public function userDoingThefollowing(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userBeingFollowed(){
        return $this->belongsTo(User::class, 'followeduser');
    }
}
