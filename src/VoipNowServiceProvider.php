<?php

namespace HyderKamran\VoipNow;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use HyderKamran\VoipNow\Adapter\SoapAdapter;

class VoipNowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/voipnow.php' => config_path('voipnow.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/../database/migrations/add_voipnow_columns_to_users.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . 'add_voipnow_columns_to_users.php'),
            ], 'migrations');
        }
    }

    /**
     * Register services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/voipnow.php', 'voipnow');

        $this->registerClient();

        $this->registerVoipNow();
    }

    public function registerClient()
    {
        $this->app->singleton('voipnow.client', function () {
            return new SoapAdapter();
        });

        $this->app->alias('voipnow.client', SoapAdapter::class);
    }

    public function registerVoipNow()
    {
        $this->app->singleton('voipnow', function (Container $app) {
            $config = $app['config'];
            $client = $app['voipnow.client'];

            return new VoipNowClass($config, $client);
        });

        $this->app->alias('voipnow', VoipNowFacade::class);
    }
}
