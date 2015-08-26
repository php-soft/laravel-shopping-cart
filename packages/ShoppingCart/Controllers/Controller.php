<?php

namespace PhpSoft\ShoppingCart\Controllers;

use Auth;
use PhpSoft\Illuminate\ArrayView\Facades\ArrayView;
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
        ArrayView::setViewPaths([ __DIR__ . '/../resources/views' ]);
    }
}
