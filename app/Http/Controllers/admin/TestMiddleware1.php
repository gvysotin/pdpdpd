<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TestMiddleware1 extends Controller
{

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        Log::info('inside TestMiddleware1 Controller');
        //dd('Break');



        // //dd('Сработал метод __invoke');
        // $user = Auth::user();

        // dump('Если мы дошли до сюда, пользователь админ, ниже объект $request');
        // dump($request);


        // $data = $request->all();
        // dump($data);

        // $user = Auth::user();

        // $data = $request->all();
        // $ip = $request->ip();

        // if ($request->isMethod('post')) {
        //     dump('Это POST-запрос');
        // } else if ($request->isMethod('get')) {
        //     dump('Это GET-запрос');
        // }

        // $method = $request->method();
        // $url = $request->url();
        // $fullUrl = $request->fullUrl();
        // $path = $request->path();
        // $server = $request->server();
        // $cookie = $request->cookie();


        // dump($cookie);
        // dump($server);
        // dump($method);
        // dump($url);
        // dump($fullUrl);
        // dump($path);

        // dump($user);
        // dump($data);
        // dump($ip);
        // dd($request);
    }
}
