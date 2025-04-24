<?php

namespace App\Providers;

use App\Contracts\UserCreatorInterface;
use App\Models\User;
use App\Services\EmailNotificationService;
use App\Services\Interfaces\EmailNotificationServiceInterface;
use App\Services\UserCreator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Contracts\UserFactoryInterface;
use App\Factories\UserFactory;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserCreatorInterface::class,
            UserCreator::class
        );

        $this->app->bind(
            UserFactoryInterface::class,
            UserFactory::class
        );

        $this->app->bind(
            EmailNotificationServiceInterface::class,
            EmailNotificationService::class
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('aaa', function (Request $request) {
            return Limit::perMinute(1)->by('aaa|' . $request->ip(). '|' . $request->userAgent());
        });

        RateLimiter::for('bbb', function (Request $request) {
            return Limit::perMinute(2)->by('bbb|' . $request->ip(). '|' . $request->userAgent());
        });

        RateLimiter::for('ccc', function (Request $request) {
            return Limit::perMinute(3)->by('ccc|' . $request->ip(). '|' . $request->userAgent());
        });

        if (config('app.is_installing')) {
            // Действия при установке приложения
        } else {

        //    if (env('APP_DEBUG') == true) {
        //        \Debugbar::enable();
        //    }
        
            // Код ниже выполняется если приложение уже установлено
            // и фраг IS_INSTALLING в .env поставлен false.
            Paginator::useBootstrapFive();


            //app()->setLocale('ru');
            //App::setLocale('ru');
            app()->getLocale();


            // Получим список 5-ти самых активных пользователей
            // $topUsers = User::withCount('ideas')
            //     ->orderBy('ideas_count', 'DESC')
            //     ->limit(5)->get();


            
            // Здесь идёт речь о глобальной переменной в шаблонизаторе Blade
            // Чтобы не писать такую логику в каждом контроллере где нужна
            // переменная $topUsers. Здесь она вычисляется один раз.
            // поделимся полученным списком со всеми Blade шаблонами
            // View::share('topUsers', $topUsers);


          // Здесь идёт речь о глобальной переменной в шаблонизаторе Blade
            // Чтобы не писать такую логику в каждом контроллере где нужна
            // переменная $topUsers. Здесь она вычисляется один раз.

            // Проблема при установке проекта с нуля, ругается на кэш:
            // кэшируем топ-5 самых активных пользователей
            // $topUsers = Cache::remember('topUsers', now()->addSeconds(60), function () {
            //     return User::withCount('ideas')
            //         ->orderBy('ideas_count', 'DESC')
            //         ->limit(5)->get();
            // });
            $topUsers = [];

            // dd($topUsers);

            // поделимся полученным списком со всеми Blade шаблонами
            View::share('topUsers', $topUsers);
        }       

    }
}




    // При установке пустой программы даже vendor не инсталируется при команде composer install
    // Пишет что табилца с кешем пустая, и дело дальше не идёт.
    //      public function boot(): void
    //     {

    //         //
    //         Paginator::useBootstrapFive();

    //         // app()->setLocale('ru');
    //         // App::setLocale('ru');

    //         // если система не видит Debugbar
    // //        if (env('APP_DEBUG') == true) {
    // //            \Debugbar::enable();
    // //        }

    //         // cache()->forget('topUsers');
    //         // Cache::forget('topUsers');

    //         // получим список 5-ти самых активных пользователей
    //         // $topUsers = User::withCount('ideas')
    //         //     ->orderBy('ideas_count', 'DESC')
    //         //     ->limit(5)->get();


    //         // Проблема при установке проекта с нуля, ругается на кэш:
    //         // кэшируем топ-5 самых активных пользователей
    //         $topUsers = Cache::remember('topUsers', now()->addSeconds(10), function () {
    //             return User::withCount('ideas')
    //                 ->orderBy('ideas_count', 'DESC')
    //                 ->limit(5)->get();

    //         });

    //         // dd($topUsers);

    //         // поделимся полученным списком со всеми Blade шаблонами
    //         View::share('topUsers', $topUsers);

    //     }