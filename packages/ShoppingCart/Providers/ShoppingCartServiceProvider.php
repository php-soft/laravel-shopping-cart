<?php

namespace PhpSoft\ShoppingCart\Providers;

use Illuminate\Support\ServiceProvider;
use PhpSoft\ShoppingCart\Commands\MigrationCommand;

class ShoppingCartServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // Set views path
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'phpsoft.shoppingcart');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/phpsoft.shoppingcart'),
        ]);

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
