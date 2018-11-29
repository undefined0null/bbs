<?php

namespace App\Http\Controllers\Api;

use App\Models\Reply;
use App\Models\Topic;
use App\Http\Requests\Api\ReplyRequest;
use Illuminate\Http\Request;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
    public function store(Topic $topic, ReplyRequest $request, Reply $reply)
    {
        $reply->content = $request->content;
        $reply->topic   = $topic->id;
        $reply->user_id = $this->user()->id();
        $reply->save();

        return $this->response->item($reply, new ReplyTransformer())
                ->setStatusCode(201);
    }
}
