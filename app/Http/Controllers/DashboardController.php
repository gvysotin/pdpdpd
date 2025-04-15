<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeEmail;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        //return new WelcomeEmail(auth()->user());

        // $ideas = Idea::withCount(['likes'])->with('user:id,name,image', 'comments.user:id,name,image')->orderBy('created_at', 'DESC');

        // $ideas = Idea::with('user:id,name,image', 'comments.user:id,name,image')->orderBy('created_at', 'DESC');

        //return "Дошли досюда, всё отлично работает!";

//
//        $ideas = Idea::all();
//        if($ideas->count()==0) {
//            // нет ни одного поста,
//        }


        $ideas = Idea::when(request()->has("search"), function (Builder $query) {
            /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\Idea $query */
            $query->search(request('search', ''));
        })->with('user:id,name,image', 'comments.user:id,name,image')->orderBy('created_at', 'DESC')->paginate(5);

//        // $ideas = Idea::with('user:id,name,image', 'comments', 'comments.user:id,name,image')->orderBy('created_at', 'DESC');



        return view('dashboard', [
            'ideas' => $ideas,
            'is_author' => 'true',
            // 'topUsers' => $topUsers
        ]);


        // if (request()->has("search")) {
        //     $ideas = $ideas->search(request('search', ''));
        // }

        // $ideas->when(request()->has("search"), function (Builder $query) {
        //     $query->search(request('search', ''));
        // });


        // $topUsers = User::orderBy('created_at', 'DESC')->limit(5)->get();
        // dd($topUsers);


        // $topUsers = User::withCount('ideas')
        // ->orderBy('ideas_count', 'DESC')
        // ->limit(5)->get();
        // dd($topUsers);




        // $aaa = Comment::where("idea_id", 138)->get();
        // dd($aaa);

        //$aaa = $idea->comments();
        //dd($aaa);

        // Это заготовка под логику, чтобы кнопки не показывались у идеи, если текущий пользователь не автор идеи
        // $is_author = false;
        // if (auth()->id() !== $idea->user_id) {
        //     $is_author = true;
        // }

        // $is_author = false;
        // if (auth()->id() == $ideas->user_id) {
        //     $is_author = true;
        // }

        // dd($ideas);



    }
}
