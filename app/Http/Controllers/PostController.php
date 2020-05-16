<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Post;
use App\Topic;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(Request $request, Topic $topic, Post $post)
    {
        return PostResource::make($post);
    }

    public function store(PostCreateRequest $request, Topic $topic)
    {
        $post = new Post;
        $post->body = $request->get('body');
        $post->user()->associate(auth()->user());

        $topic->posts()->save($post);

        return PostResource::make($post);
    }

    public function update(PostUpdateRequest $request, Topic $topic, Post $post)
    {

        $this->authorize('update', $post);

        $post->body = $request->get('body');
        $post->save();
        return PostResource::make($post);
    }

    public function destroy(Request $request, Topic $topic, Post $post)
    {
        $this->authorize('destroy', $post);

        $post->delete();

        return response(null, 204);
    }
}
