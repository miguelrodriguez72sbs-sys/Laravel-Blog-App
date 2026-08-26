<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function likeOrUnlike($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $like = Like::where('post_id', $id)->where('user_id', auth()->user()->id)->first();

        // Si no ha dado me gusta, lo crea; si ya existe, lo quita (Toggle)
        if (!$like) {
            Like::create([
                'post_id' => $id,
                'user_id' => auth()->user()->id
            ]);

            return response()->json(['message' => 'Liked'], 200);
        }

        $like->delete();
        return response()->json(['message' => 'Unliked'], 200);
    }
}