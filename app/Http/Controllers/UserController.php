<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
   public function storeAvatar(Request $request){
    $request->validate([
        'avatar' => 'required|image|max:3000'
    ]);
    $user = auth()->user();

    $filename = $user->id.'-'.uniqid().'.jpg';

    $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
    Storage::put('public/avatars/'.$filename, $imgData);
    
    $oldAvatar = $user->avatar;

    $user->avatar = $filename;
    $user->save();

    if($oldAvatar != "/fallback-aviator.jpg"){

        Storage::delete(str_replace("/storage/","public/",$oldAvatar));
    }
        return back()->with('success','done avatar uploaded!');
   }
   
    public function showAvatarForm(){

    return view('avatar-form');
   }

   private function getSharedData($logeduser){

    $currentlyFollowing =0;
    if(auth()->check()){
        $currentlyFollowing= Follow::where([['user_id','=', auth()->user()->id],['followeduser','=', $logeduser->id]])->count();
    }

    View::share('sharedData',['currentlyFollowing' => $currentlyFollowing, 'avatar'=>$logeduser->avatar,'username'=> $logeduser->username, 'postsCount'=> $logeduser->posts()->count(), 'followerCount'=>$logeduser->followers()->count(), 'followingCount'=>$logeduser->followingTheseUsers()->count() ]);
   }

   public function profile(User $logeduser){

    $this->getSharedData($logeduser);
    return view('profile-posts',[ 'posts'=> $logeduser->posts()->latest()->get() ]);
   
    }

    public function profileRaw(User $logeduser){

        $this->getSharedData($logeduser);
        return view('profile-posts',[ 'posts'=> $logeduser->posts()->latest()->get() ]);
       
        }

    public function profileFollowers(User $logeduser){

        $this->getSharedData($logeduser);
        return view('profile-followers',[ 'followers'=> $logeduser->followers()->latest()->get()]);
       
     }

    public function profileFollowing(User $logeduser){
        
        $this->getSharedData($logeduser);
        return view('profile-following',['following'=> $logeduser->followingTheseUsers()->latest()->get()]);
       
     }

        

    public function logout(){
    auth()->logout();
    return redirect('/')->with('success','You have now logout');
   }
   
    public function showCorrectHomepage(){

    if(auth()->check()){
        return view('homepage-feed',['posts'=> auth()->user()->feedPosts()->latest()->paginate(5)]);
    }

    else {
        return view ('homepage');
    }

   }
   
    public function login(Request $request){
    $incomingFields = $request->validate([
        'loginusername'=>'required',
        'loginpassword'=>'required'

    ]);

    if(auth()->attempt(['username'=> $incomingFields['loginusername'],'password'=> $incomingFields['loginpassword']])){

        $request->session()->regenerate();
        return redirect('/')->with('success','You have successfull login');
    }
    else{
        return redirect('/')->with('failure','invalid log in');
    }
   }

    public function register(Request $request){
    $incomingFields = $request->validate([
        'username'=>['required', 'min:3','max:20', Rule::unique('users','username')],
        'email'=>['required','email',Rule::unique('users','email')],
        'password'=>['required','min:8','confirmed']
    ]);

    $incomingFields['password'] = bcrypt($incomingFields['password']);
    $user = User::create($incomingFields);
    auth()->login($user);
    return redirect('/')->with('success', 'Thank you for creating an account');
   }
}
