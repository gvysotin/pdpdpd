<?php

namespace App\Providers;


use App\Application\Registration\Contracts\RegisterUserHandlerInterface;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\Factories\UserFactory;
use App\Domain\Registration\Services\UserCreator;
use App\Domain\Registration\Specifications\UniqueEmailSpecification;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Infrastructure\Registration\Repositories\EloquentUserRepository;
use App\Infrastructure\Registration\Services\EmailNotificationService;
use App\Infrastructure\Shared\Transaction\LaravelTransactionManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


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

        $this->app->bind(
            EmailSpecificationInterface::class,
            UniqueEmailSpecification::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(
            TransactionManagerInterface::class, 
            LaravelTransactionManager::class
        );


        $this->app->bind(
            RegisterUserHandlerInterface::class, 
            RegisterUserCommandHandler::class
        );        

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

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