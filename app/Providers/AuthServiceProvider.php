<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        // Gate => Permission | Simple Role

        // Role (Шлюз роли)
        Gate::define('admin', function(User $user) : bool {
            return (bool) $user->is_admin;
        });


        // Permission (Мы так же можем использовать это как разрешение)

        // // Разрешение (Permission) для удаления идеи
        // Gate::define('idea.delete', function(User $user, Idea $idea) : bool {
        //     return ((bool) $user->is_admin || $user->id === $idea->user_id); // только администратор или владелец
        // });

        // // Разрешение (Permission) для редактирования идеи
        // Gate::define('idea.edit', function(User $user, Idea $idea) : bool {
        //     return ((bool) $user->is_admin || $user->id === $idea->user_id); // только администратор или владелец
        // });
    }
}
