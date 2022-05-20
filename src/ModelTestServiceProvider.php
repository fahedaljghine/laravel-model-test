<?php

namespace Fahedaljghine\ModelTest;

use Illuminate\Support\ServiceProvider;

class ModelTestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/model-test.php' => config_path('model-test.php'),
        ], 'config');


        $stubs_path = base_path('stubs/laravel-model-test') ;
        if (!file_exists($stubs_path)) {
            mkdir($stubs_path, 0755, true);
        }

        $this->publishes([
            __DIR__ . '/Commands/stubs' => base_path('stubs/laravel-model-test'),
        ], 'stubs');

        $this->commands([
            \Fahedaljghine\ModelTest\Commands\GenerateTests ::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/model-test.php', 'model-test');
    }

}
