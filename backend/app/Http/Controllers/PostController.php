<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\PostLike;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $posts = Post::query()
            ->with('user:id,name,avatar')
            ->withCount(['likes', 'comments'])
            ->latest('id')
            ->paginate(20);

        if ($request->user()) {
            $posts->getCollection()->each(function (Post $post) use ($request) {
                $post->setAttribute('liked_by_user', $post->likes()->where('user_id', $request->user()->id)->exists());
            });
        }

        return response()->json($posts);
    }

    public function toggleLike(Request $request, Post $post): JsonResponse
    {
        $like = PostLike::query()->where('post_id', $post->id)->where('user_id', $request->user()->id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            PostLike::create(['post_id' => $post->id, 'user_id' => $request->user()->id]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }

    public function comments(Post $post): JsonResponse
    {
        return response()->json([
            'data' => $post->comments()->with('user:id,name')->latest('id')->get(),
        ]);
    }

    public function storeComment(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $post->comments()->create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);
        $comment->load('user:id,name');

        return response()->json($comment, 201);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post = $request->user()->posts()->create($validated);
        $post->load('user:id,name,avatar');

        return response()->json($post, 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $posts = $request->user()->posts()
            ->with('user:id,name,avatar')
            ->withCount(['likes', 'comments'])
            ->latest('id')
            ->get();

        $posts->each(function (Post $post) use ($request) {
            $post->setAttribute('liked_by_user', $post->likes()->where('user_id', $request->user()->id)->exists());
        });

        return response()->json(['data' => $posts]);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        Gate::forUser($request->user())->authorize('update', $post);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post->update($validated);
        $post->load('user:id,name,avatar');

        return response()->json($post);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        Gate::forUser($request->user())->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post berhasil dihapus.']);
    }
}
