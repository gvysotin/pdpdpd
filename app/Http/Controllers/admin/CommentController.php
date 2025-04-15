<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index()
    {

        $comments = Comment::with('user:id,name,image', 'idea:id')->latest()->paginate(15);

        // dd($comments);

        return view('admin.comments.index', compact('comments'));

    }



    public function destroy(Comment $comment)
    {
        // dd($comment);

        $comment->delete();



        return redirect()->route('admin.comments.index')->with("success","Comment deleted succesfully !!!");;

    }


}


