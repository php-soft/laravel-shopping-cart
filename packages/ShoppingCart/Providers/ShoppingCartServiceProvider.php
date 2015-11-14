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

        // Publish config & views
        $this->publishes([
            __DIR__ . '/../config/phpsoft-shoppingcart.php' => config_path('phpsoft.shoppingcart.php'),
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
        $this->mergeConfigFrom(
            __DIR__ . '/../config/phpsoft.shoppingcart.php', 'phpsoft.shoppingcart'
        );

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
