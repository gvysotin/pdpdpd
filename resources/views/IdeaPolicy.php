<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;

class IdeaPolicy
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
    public function update(User $user, Idea $idea): bool
    {
        return (bool) $user->is_admin || $user->is($idea->user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Idea $idea): bool
    {
        return (bool) $user->is_admin || $user->is($idea->user);
    }


}
