<?php

namespace PhpSoft\Illuminate\ShoppingCart\Controllers;

use Auth;
use App\Http\Controllers\Controller as AppController;

class Controller extends AppController
{
    /**
     * Instantiate a new Controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $app = app();
        $app['view']->addLocation(__DIR__.'/../resources/views');
    }

    /**
     * Check authentication
     * 
     * @return boolean
     */
    public function checkAuth()
    {
        return !empty(Auth::user());
    }

    /**
     * Check permission
     * 
     * @return boolean
     */
    public function checkPermission($permission)
    {
        return Auth::user()->can($permission) || Auth::user()->hasRole('admin');
    }
}
