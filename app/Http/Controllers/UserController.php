<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $ideas = [];

        $ideas = $user->ideas()->paginate(5);

        return view("users.show", compact("user", "ideas"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // dd($user);
        $this->authorize('update', $user);


        $ideas = [];

        $ideas = $user->ideas()->paginate(5);


        $editing = true;
        return view("users.edit", compact("user", "ideas", "editing"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // dd( $request);
        // dd($user);

        $this->authorize('update', $user);

        $validated = $request->validated();
        // $validated = request()->validate([
        //     "name" => "required|min:5|max:40",
        //     "bio" => "required|min:1|max:255",
        //     "image" => "image"
        // ]);

        if(request()->has("image")) {
            $imagePath = request()->file('image')->store('profile', 'public');
            $validated['image'] = $imagePath;

            Storage::disk('public')->delete($user->image ?? '');
        }

        // Нужно добавить обработку ошибок:
        // try {
        //     // код загрузки
        // } catch (\Exception $e) {
        //     Log::error('Image upload failed: '.$e->getMessage());
        //     return back()->withError('Image upload failed');
        // }



        $user->update($validated);

        return redirect()->route("profile2", $user->id)->with("success","User data has been successfully updated.");
        // return redirect()->route("profile")->with("success","User data has been successfully updated.");

    }

    public function profile()
    {
        //return $this->show(auth()->user());
        return $this->show(Auth::user());
    }

    //
    public function profile2(User $user)
    {
        return $this->show($user);
    }

}
