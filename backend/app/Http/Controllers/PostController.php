<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::query()
            ->with('user:id,name')
            ->latest('id')
            ->paginate(20);

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post = $request->user()->posts()->create($validated);
        $post->load('user:id,name');

        return response()->json($post, 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $posts = $request->user()->posts()
            ->with('user:id,name')
            ->latest('id')
            ->get();

        return response()->json(['data' => $posts]);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', $post);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post->update($validated);
        $post->load('user:id,name');

        return response()->json($post);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        Gate::forUser($request->user())->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post berhasil dihapus.']);
    }
}
