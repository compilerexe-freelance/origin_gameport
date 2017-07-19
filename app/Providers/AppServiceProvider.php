<?php

namespace App\Providers;

use App\Models\Listing;
use App\Observers\ListingObserver;
use App\Models\Game;
use App\Observers\GameObserver;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Listing::observe(ListingObserver::class);
        Game::observe(GameObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      if ($this->app->environment() == 'local') {
      // Jeffrey Way's generators
          $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
      // Backpack generators
          $this->app->register('Backpack\Generators\GeneratorsServiceProvider');
      }
    }



}
