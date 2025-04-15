<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCommentRequest;

class CommentController extends Controller
{
    //


    public function store(CreateCommentRequest $request, Idea $idea)
    {
        $validated = $request->validated();
        // $validated = request()->validate([
        //     "content" => "required|min:3|max:240",
        // ]);

        $validated['user_id'] = Auth::id();
        $validated['idea_id'] = $idea->id;

        // $arr = [];
        // $arr [] = $validated;
        // $arr [] = $validated['user_id'];
        // $arr [] = $validated['idea_id'];
        // dd($arr);
        //

        $comment = new Comment;
        $comment->user_id = $validated['user_id'];
        $comment->idea_id = $validated['idea_id'];
        $comment->content = $validated['content'];
        $comment->save(); // сохранили в базу данных объект

        // Comment::create($validated);

        return redirect()->route("ideas.show", $idea->id)->with("success","Comment posted successfully!!!");
    }

    // public function store(Idea $idea)
    // {
    //     $comment = new Comment; // тут получается экземпляр модели

    //     //dd(request()->get("content"));
    //     //dd(request("content"));
    //     //dd(request()->content);

    //     $comment->user_id = auth()->id();
    //     $comment->idea_id = $idea->id; // таким образом установили наши отношения
    //     //$comment->content = request()->get("content"); // подгрузили содержимое переменной content
    //     //$comment->content = request("content");

    //     $comment->content = request()->content;
    //     $comment->save(); // сохранили в базу данных объект

    //     return redirect()->route("ideas.show", $idea->id)->with("success","Comment posted successfully!!!");
    // }
}
