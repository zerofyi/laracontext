<?php

namespace Zerofyi\LaraContext;

use Illuminate\Support\ServiceProvider;
use Zerofyi\LaraContext\Commands\GenerateAIContext;

class LaraContextServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateAIContext::class,
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Left intentionally clean to optimize bootstrap runtimes
    }
}