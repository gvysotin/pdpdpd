<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // $arr = [];
        // $arr [] = $user;
        // $arr [] = $model;
        // dd ($arr);

        return $user->is($model) || $user->is_admin;
        //return true;
    }

}
