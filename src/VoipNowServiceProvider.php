<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use HyderKamran\VoipNow\Adapter\RestAdapter;
use HyderKamran\VoipNow\Adapter\SoapAdapter;
use HyderKamran\VoipNow\Commands\CheckConnectionCommand;
use HyderKamran\VoipNow\Enums\AdapterType;
use HyderKamran\VoipNow\Contracts\ConnectorInterface;
use HyderKamran\VoipNow\VoipNowSoapClient;

class VoipNowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/voipnow.php' => config_path('voipnow.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations/add_voipnow_columns_to_users.php.stub'
                    => database_path('migrations/' . date('Y_m_d_His', time()) . '_add_voipnow_columns_to_users.php'),
            ], 'migrations');

            $this->commands([
                CheckConnectionCommand::class,
            ]);
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/voipnow.php', 'voipnow');

        $this->registerRestAdapter();
        $this->registerSoapAdapter();
        $this->registerRestClient();
        $this->registerSoapClient();
    }

    /**
     * Bind the REST adapter as the default ConnectorInterface implementation.
     */
    protected function registerRestAdapter(): void
    {
        $this->app->singleton(ConnectorInterface::class, function () {
            $adapterValue = $this->app['config']->get('voipnow.adapter', 'rest');
            return AdapterType::fromString($adapterValue)->isSoap()
                ? new SoapAdapter()
                : new RestAdapter();
        });
    }

    /**
     * Bind the SOAP adapter separately so it's always available
     * regardless of the configured default adapter.
     */
    protected function registerSoapAdapter(): void
    {
        $this->app->singleton(SoapAdapter::class, function () {
            return new SoapAdapter();
        });
    }

    /**
     * Register the main REST/UnifiedAPI v5 client as 'voipnow'.
     */
    protected function registerRestClient(): void
    {
        $this->app->singleton('voipnow', function (Container $app) {
            return new VoipNowClient(
                $app['config'],
                $app[ConnectorInterface::class],
                $app[VoipNowSoapClient::class]
            );
        });

        $this->app->alias('voipnow', VoipNowFacade::class);
    }

    /**
     * Register the SystemAPI SOAP client as 'voipnow.soap'.
     */
    protected function registerSoapClient(): void
    {
        $this->app->singleton(VoipNowSoapClient::class, function (Container $app) {
            return new VoipNowSoapClient(
                $app['config'],
                $app[SoapAdapter::class]
            );
        });

        $this->app->alias(VoipNowSoapClient::class, 'voipnow.soap');
    }
}
