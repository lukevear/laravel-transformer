<?php

namespace LukeVear\LaravelTransformer;

use Illuminate\Support\ServiceProvider;

class TransformerServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $path = realpath(__DIR__ . '/../config/config.php');
        $this->publishes([$path => config_path('laravel-transformer.php')], 'config');
        $this->mergeConfigFrom($path, 'laravel-transformer');

        // Expose the 'transform' function
        require realpath(__DIR__ . '/functions.php');
    }
}
