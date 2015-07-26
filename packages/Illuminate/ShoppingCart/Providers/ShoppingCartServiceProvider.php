<?php

namespace PhpSoft\Illuminate\ShoppingCart\Providers;

use Illuminate\Support\ServiceProvider;
use PhpSoft\Illuminate\ShoppingCart\Commands\MigrationCommand;

class ShoppingCartServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // Register commands
        $this->commands('phpsoft.shoppingcart.command.migration');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->bindShared('phpsoft.shoppingcart.command.migration', function ($app) {
            return new MigrationCommand();
        });
    }
}
