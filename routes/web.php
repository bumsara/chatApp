<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route:: get('/admins-only', function(){
    return 'Only admin should be able to see this page';
    })->middleware('can:visitAdminPages');
//user related route
Route::get('/', [UserController::class,"showCorrectHomepage"])->name('login');
Route::post('/register',[UserController::class, "register"]);
Route::post('/login',[UserController::class, "login"]);
Route::post('/logout',[UserController::class, "logout"]);
Route::get('/manage-avatar', [UserController::class, "showAvatarForm"])->middleware('auth');
Route::post('/manage-avatar', [UserController::class, "storeAvatar"])->middleware('auth');

//follows related routes
Route::post('/create-follow/{person:username}',[FollowController::class, 'createFollow']);
Route::post('/remove-follow/{person:username}',[FollowController::class, 'removeFollow']);

//blog post related route
Route::get('/create-post',[PostController::class, "showCreateForm"])->middleware('auth');
Route::post('/create-post',[PostController::class, "storeNewpost"])->middleware('auth');
Route::get('/post/{post}',[PostController::class, "viewSinglePost"]);
Route::delete('/post/{post}',[PostController::class,"futa"])->middleware('can:delete,post');
Route::get('/post/{post}/edit',[PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}',[PostController::class, 'actualyUpdate'])->middleware('can:update,post');
Route::get('/search/{term}', [PostController::class, 'search']);

//profile related route
Route::get('/profile/{logeduser:username}', [UserController::class, 'profile'])->middleware('auth');
Route::get('/profile/{logeduser:username}/followers', [UserController::class, 'profileFollowers'])->middleware('auth');
Route::get('/profile/{logeduser:username}/following', [UserController::class, 'profileFollowing'])->middleware('auth');

Route::get('/profile/{logeduser:username}/raw', [UserController::class, 'profileRaw'])->middleware('auth');
Route::get('/profile/{logeduser:username}/followers/raw', [UserController::class, 'profileFollowersRaw'])->middleware('auth');
Route::get('/profile/{logeduser:username}/following/raw', [UserController::class, 'profileFollowingRaw'])->middleware('auth');

//chat related route
Route::post('/send-chat-message',function (Request $request){
    $formFields = $request->validate([
        'textvalue'=>'required'
    ]);

    if (!trim(strip_tags($formFields['textvalues']))) {
        return response()->noContent();
    }

    broadcast(new ChatMessage(['username'=>auth()->user()->username, 'textvalue'=>strip_tags($request->textvalue), 'avatar'=> auth()->user()->avatar]))->toOthers();
    return response()->noContent();
})->middleware('auth');