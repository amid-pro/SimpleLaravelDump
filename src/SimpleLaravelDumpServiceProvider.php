<?php

namespace AmidPro\SimpleLaravelDump;

use Illuminate\Support\ServiceProvider;

class SimpleLaravelDumpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([SimpleLaravelDump::class]);
    }
}