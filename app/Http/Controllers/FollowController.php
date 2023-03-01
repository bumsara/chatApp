<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $person){
        //you can not follow yourself
        if($person->id == auth()->user()->id){
            return back()->with('failure', 'you cant follow yourself');
        }
        //you cant follow someone youve alread y follow
        $existCheck = Follow::where([['user_id','=',auth()->user()->id], ['followeduser', '=', $person->id]])->count();

        if($existCheck){
            return back()->with('failure', 'You are already followed this guy');
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $person->id;
        $newFollow->save();

       return back()->with('success','User successfully followed.');
    }

    public function removeFollow(User $person){
        Follow::where([['user_id','=',auth()->user()->id],['followeduser','=',$person->id]])->delete();

       return back()->with('success','user successfully unfollowed');

    }
}
