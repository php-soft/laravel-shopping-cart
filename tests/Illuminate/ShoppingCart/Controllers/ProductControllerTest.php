<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class ProductControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testCreateValidateFailure()
    {
        $response = $this->call('POST', '/products', [ 'title' => 'asds' ]);

        dump($response);
    }
}
