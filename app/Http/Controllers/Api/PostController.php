<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $posts = Post::with('user')
            ->withCount(['comments', 'likes']) // Trae likes_count y comments_count
            ->orderBy('created_at', 'desc')
            ->get();

        // Validamos si el usuario actual le dio 'like'
        $posts->map(function ($post) use ($userId) {
            $post->self_liked = $userId 
                ? $post->likes()->where('user_id', $userId)->exists() 
                : false;
            return $post;
        });

        return response()->json($posts, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'body'  => 'required|string',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data = [
            'title' => $request->filled('title') ? $request->title : 'Sin título',
            'body'  => $request->body,
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
            $data['image'] = $imagePath;
        }

        $post = $request->user()->posts()->create($data);

        return response()->json($post->load('user'), 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load('user'), 200);
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id != Auth::id()) {
            return response()->json(['message' => 'No autorizado para actualizar este post'], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'body'  => 'required|string',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data = [
            'title' => $request->filled('title') ? $request->title : $post->title,
            'body'  => $request->body,
        ];

        if ($request->hasFile('image')) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            $path = $request->file('image')->store('posts', 'public');
            $data['image'] = $path;
        }

        $post->update($data);

        return response()->json([
            'message' => 'Post actualizado con éxito',
            'post'    => $post->load('user')
        ], 200);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id != Auth::id()) {
            return response()->json(['message' => 'No autorizado para eliminar este post'], 403);
        }

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['message' => 'Post eliminado con éxito'], 200);
    }
}