<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    // Post create
    public function create(Request $request){
        $post = new Post;
        $post->user_id = Auth::user()->id;
        $post->desc    = $request->desc;
        // check if post has photo
        if($request->photo != ''){
            // gan ten vs time 
            $photo = time().'.jpg';
            // link storage 
            file_put_contents('storage/posts/'.$photo,base64_decode($request->photo));
            $post->photo = $photo;
        }
        $post->save();
        $post->user;
        return response()->json([
            'success'   => true,
            'message'   =>'posted',
            'post'      => $post
        ]);
    }

    // Post update
    public function update(Request $request){
        $post = Post::find($request->id);
        // Check if user is editing his own post
        // Kiem tra user id voi post user id

        if(Auth::user()->id != $post->user_id){
            return response()->json([
                'success' => false,
                'message' => 'unauthorrized access'
            ]);
        }
        $post->desc = $request->desc;
        $post->update();
        return response()->json([
            'success'   => true,
            'message'   => 'post edited'
        ]);
    }

    // Post delete
    public function delete(Request $request){
        $post = Post::find($request->id);
        // Check if user is editing his own post
        // kiem tra id nguoi post -> xoa
        if(Auth::user()->id != $post->user_id){
            return response()->json([
                'success' => false,
                'message' => 'unauthorrized access'
            ]);
        }
        //  Check if post has photo to delete
        if( $post->photo != ''){
            Storage::delete('public/posts'.$post->photo);
        }
        $post->delete();
        return response()->json([
            'success'   => true,
            'message'   => 'post deleted'
        ]);
    }

    // posts infor
    public function posts(){
        $posts =Post::orderBy('id','desc')->get();
        foreach($posts as $post){
            // get user of post
            $post-> user;
            // comment count
            $post['commentsCount'] = count($post->comments);
            // likes count
            $post['likesCount'] = count($post->likes);

            // check if user liked his own post
            $post['selfLike'] = false;
            foreach($post->likes as $like){
                if($like->user_id == Auth::user()->id){
                    $post['selfLike']= true;
                }
            }
        }
        return response()->json([
            'success'   => true,
            'posts'     => $posts
        ]);

    }

    public function myPosts(){
        $posts = Post::where('user_id', Auth::user()->id)->orderBy('id','desc')->get();
        $user  = Auth::user();
        return response()->json([
            'success' => true,
            'posts'   => $posts,
            'user'    => $user
        ]);
    }


}
