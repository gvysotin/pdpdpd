<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user(); // и это даст нам объект пользователя который вошел в систему

        $followingIDs = $user->followings()->pluck('user_id');

        // dd($followingIDs);

        // $ideas = Idea::with('user:id,name,image', 'comments.user:id,name,image')->orderBy('created_at', 'DESC');

        $ideas = Idea::with('user:id,name,image', 'comments.user:id,name,image')->whereIn('user_id', $followingIDs)->latest();

        // if (request()->has("search")) {
        //     $ideas = $ideas->where("content", "like", "%" . request()->get("search", "") . "%");
        // }

        if (request()->has("search")) {
            $ideas = $ideas->search(request('search', ''));
        }


        return view('dashboard', [
            'ideas' => $ideas->paginate(5),
            'is_author' => 'true'
        ]);
    }
}
