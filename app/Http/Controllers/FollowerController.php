<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{

    public function follow(User $user)
    {
        // dd($user);
        $follower = Auth::user(); // опредляем аутентифицированного пользователя
        $follower->followings()->attach($user);
        // первый метод даёт понять что выбрано отношение followings.
        // второй метод создаёт запись, в него мы передаём id на кого подписываемся или сам объект user.
        return redirect()->route("users.show", $user->id)->with("success","Followed successfully!");
    }

    public function unfollow(User $user)
    {
        // dd($user);
        $follower = Auth::user(); // опредляем аутентифицированного пользователя
        $follower->followings()->detach($user);
        // первый метод даёт понять что выбрано отношение followings.
        // второй метод создаёт запись, в него мы передаём id на кого подписываемся или сам объект user.
        return redirect()->route("users.show", $user->id)->with("success","UnFollowed successfully!");

    }


}
