<?php

namespace MSBTheme;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use MSBTheme\Console\InstallMsbTheme;

class MSBThemeServiceProvider extends ServiceProvider 
{
    public function register()
    {
    }

    public function boot(Kernel $kernel)
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/theme' => public_path('themes'),
                ], 'assets');

            $this->commands([
                InstallMsbTheme::class
            ]);
        }

    }

    public function isDeferred()
    {
        return false;
    }
}

?>
