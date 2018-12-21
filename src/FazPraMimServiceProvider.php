<?php

namespace Louisk\ArtisanFazPraMim;

use Louisk\ArtisanFazPraMim\BaseFiles\Requests\Request;
use Louisk\ArtisanFazPraMim\Commands\CrudCommand;
use Illuminate\Support\ServiceProvider;

class FazPraMimServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/faz_pra_mim.php' => config_path('faz_pra_mim.php'),
        ], 'faz-pra-mim');

        $this->publishes([
            __DIR__ . '/../publish/app.blade.php' => base_path('resources/views/layouts/app.blade.php'),
        ]);

        $this->publishes([
            __DIR__ . '/stubs/' => base_path('resources/faz-pra-mim/'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Reliese\Coders\CodersServiceProvider::class);

        $this->commands(
            CrudCommand::class
        );
    }
}
