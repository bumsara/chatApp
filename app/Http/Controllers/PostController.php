<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //

    public function search($term){
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    public function actualyUpdate(Post $post, Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);
        $incomingFields['title']= strip_tags($incomingFields['title']);
        $incomingFields['body']= strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return back()->with('success','post successfully updated.');
    }

    public function showEditForm(Post $post){

        return view('edit-post',['post'=> $post]);
    }

    public function futa(Post $post){

        $post->delete();
        return redirect('/profile/'.auth()->user()->username)->with('success','Post successfully deleted');
    }


    public function viewSinglePost(Post $post){
        
        $post['body']= strip_tags(Str::markdown($post->body), '<p><ul><ol><li><strong><em><h3><br>');
        return view('single-post', ['post'=>$post]);
    }

    public function storeNewpost(Request $request){
        $incomingFields = $request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);
        $incomingFields['title']= strip_tags($incomingFields['title']);
        $incomingFields['body']= strip_tags($incomingFields['body']);
        $incomingFields['user_id']= auth()->id();

       $newpost= Post::create($incomingFields);

        return redirect("/post/{$newpost->id}")->with('success','new post succesfull created');

    }
    
    public function showCreateform(){
       
        return view('create-post');
    }

}
