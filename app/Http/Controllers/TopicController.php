<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicCreateRequest;
use App\Http\Requests\TopicUpdateRequest;
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

    public function update(TopicUpdateRequest $request, Topic $topic)
    {
        //通过TopicPolicy的update方法检测当前用户是否有权限执行操作。
        $this->authorize('update', $topic);

        $topic->title = $request->get('title');
        $topic->save();
        return TopicResource::make($topic);
    }

    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);

        $topic->delete();

        return response()->json(['messages' => 'success deleted'], 202);
    }
}
