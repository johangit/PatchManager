<?php

namespace JohanCode\PatchRunner;

use Illuminate\Support\ServiceProvider;
use JohanCode\PatchRunner\Console\Commands\MakePatchCommand;
use JohanCode\PatchRunner\Console\Commands\PatchCommand;
use JohanCode\PatchRunner\Console\Commands\PatchStatusCommand;

class PatchRunnerServiceProvider extends ServiceProvider
{
    public $bindings = [
        PatchRunner::class => PatchRunner::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/patch-runner.php', 'patch-runner'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/patch-runner.php' => config_path('patch-runner.php'),
        ], 'patch-runner-config');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PatchCommand::class,
                MakePatchCommand::class,
                PatchStatusCommand::class,
            ]);
        }
    }
}
