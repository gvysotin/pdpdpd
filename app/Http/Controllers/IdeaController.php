<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIdeaRequest;
use App\Http\Requests\UpdateIdeaRequest;
use App\Models\Idea;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller
{
    //

    public function create()
    {
        return 'это сообщение сделано методом App\Http\Controllers\IdeaController::create';
    }

    public function show(Idea $idea)
    {
        //$idea->get();

        // return view("ideas.show", [
        //     "idea" => $idea
        // ]);

        // $zzz = $idea->comments;
        // dd($zzz);

        $is_author = false;
        if (Auth::id() == $idea->user_id) {
            $is_author = true;
        }

        // $topUsers = User::withCount('ideas')
        // ->orderBy('ideas_count', 'DESC')
        // ->limit(5)->get();

        // return view("ideas.show", compact("idea", "is_author", "topUsers"));
        return view("ideas.show", compact("idea", "is_author"));
    }


    public function store(CreateIdeaRequest $request)
    {
        //echo "<h1>Сработал метод store() в контроллере IdeaController. Параметры пока как вывести сюда не знаю!</h1>";
        //dump($_POST);
        //dump(request()->get("idea", ""));

        // request()->validate([
        //     "content" => "required|min:5|max:240",
        // ]);

        $validated = $request->validated();
        // $validated = request()->validate([
        //     "content" => "required|min:5|max:240",
        // ]);

        // $validated["user_id"] = auth()->user()->id;
        // $validated["user_name"] = auth()->user()->name;
        // $validated["user_email"] = auth()->user()->email;
        // $validated["user_password"] = auth()->user()->password;
        // dd($validated);

        $validated["user_id"] = Auth::id();


        // dump(request()->all());
        // dd($validated);
        // dd(request()->all()); // такая строка возвращает все переменные какие передал пользователь в форме

        Idea::create($validated);


        // $idea = Idea::create(
        //     [
        //         'content' => request()->get("content", ""),
        //     ]
        // );

        return redirect()->route('dashboard')->with('success', 'Idea created succesfully');


        // if($idea->save()) {
        //     echo "<h1>Успешно добавлена запись с такими данными: " . request()->get("idea", "") . "</h1>";
        // }
    }

    public function destroy(Idea $idea)
    {
        // where id = 1

        // $idea = Idea::where("id", $id)->firstOrFail();

        // $idea->delete();

        //$idea = Idea::where("id", $id)->firstOrFail()->delete();


        // проверка является авторизованный пользователем автором идеи
        // if(auth()->id() !== $idea->user_id) {
        //     abort(404);
        // }

        $this->authorize('delete', $idea);

        $idea->delete();

        return redirect()->route("dashboard")->with("success", "Idea deleted succesfully !!!");
    }

    public function edit(Idea $idea)
    {

        // проверка является авторизованный пользователем автором идеи
        // if (auth()->id() !== $idea->user_id) {
        //     abort(404);
        // }


        //$idea->get();

        // return view("ideas.show", [
        //     "idea" => $idea
        // ]);

        $this->authorize('update', $idea);

        $editing = true;

        return view("ideas.show", compact("idea", "editing"));
    }


    public function update(UpdateIdeaRequest $request, Idea $idea)
    {

        // request()->validate([
        //     "content" => "required|min:5|max:240",
        // ]);

        // request()->validate([
        //     "content" => "required|min:5|max:240",
        // ]);

        // проверка является авторизованный пользователем автором идеи
        // if (auth()->id() !== $idea->user_id) {
        //     abort(404);
        // }

        $this->authorize('update', $idea);

        $validated = $request->validated();
        // $validated = request()->validate([
        //     "content" => "required|min:5|max:240",
        // ]);

        // $validated["user_id"] = auth()->id();



        // dump(request()->all());
        // dd($validated);

        // $idea->content = request()->get("content", "");
        // // $idea->likes = request()->get("likes", "");
        // $idea->save();

        $idea->update($validated);


        return redirect()->route("ideas.show", $idea->id)->with("success", "Idea updated succcessfully!!!");
        // return view("ideas.show", compact("idea"));
    }
}
