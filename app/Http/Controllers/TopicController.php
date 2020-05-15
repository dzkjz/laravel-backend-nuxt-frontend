<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicCreateRequest;
use App\Http\Resources\TopicResource;
use App\Post;
use App\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::latestFirst()->paginate(5);

        return TopicResource::collection($topics);
    }

    public function store(TopicCreateRequest $request)
    {
        $topic = new Topic;
        $topic->title = $request->get('title');
        $topic->user()->associate($request->user());

        $post = new Post;
        $post->body = $request->get('body');
        $post->user()->associate($request->user());

        $topic->save();
        $topic->posts()->save($post);

        return TopicResource::make($topic);
    }

    public function show(Topic $topic)
    {
        return TopicResource::make($topic);
    }
}
