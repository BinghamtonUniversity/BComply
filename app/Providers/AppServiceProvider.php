<?php

namespace App\Providers;


use App\GroupMembership;
use App\ModuleAssignment;
use App\Observers\GroupMembershipObserver;
use App\Observers\ModuleAssignmentObserver;
use App\Observers\UserObserver;
use App\Observers\SimpleUserObserver;
use App\User;
use App\SimpleUser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        SimpleUser::observe(SimpleUserObserver::class);
        GroupMembership::observe(GroupMembershipObserver::class);
        ModuleAssignment::observe(ModuleAssignmentObserver::class);
        \Str::macro('snakeToTitle', function($value) {
            return \Str::title(str_replace('_', ' ', $value));
        });
    }
}
