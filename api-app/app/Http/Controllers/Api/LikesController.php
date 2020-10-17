<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Like;
use Illuminate\Support\Facades\Auth;

class LikesController extends Controller
{
    // Like and unlike
    public function like(Request $request){
        $like = Like::where('post_id',$request->id)->where('user_id', Auth::user()->id)->get();
        // check if return 0 then this post is not like and should be like else unlike
        if(count($like) > 0){
            // likes more than one
            $like[0] ->delete();
            return response()->json([
                'succcess' => true,
                'message'  => 'unliked'
            ]);
        }
        $like = new Like;
        $like->user_id = Auth::user()->id;
        $like->post_id = $request->id;
        $like->save();
        return response()->json([
            'succcess' => true,
            'message'  => 'liked'
        ]);
    }
}
