<?php

namespace DumpNamespace;

use App\Models\File;
use Illuminate\Support\ServiceProvider;

class ObserversProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
       // TODO
        File::observe(FileObserver::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
