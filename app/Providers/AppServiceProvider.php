<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Twitch\Provider as TwitchProvider;

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
        $this->bootTwitchSocialite();
    }

    private function bootTwitchSocialite()
    {
        // Fix the bug of Twitch Socialite Provider
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'Twitch',
            function ($app) use ($socialite) {
                $config = $app['config']['services.twitch'];
                return $socialite->buildProvider(TwitchProvider::class, $config);
            }
        );
    }
}
