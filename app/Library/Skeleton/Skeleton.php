<?php

namespace App\Library\Skeleton;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use View;

class Skeleton extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
		
		View::composer('*', function($view){
			$view->with('theme', config('site.theme'));
		});
    }
}
