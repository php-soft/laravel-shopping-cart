<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class ProductControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testCreateValidateFailure()
    {
        $res = $this->call('POST', '/products');
        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertEquals('The title field is required.', $results->first_message);
        $this->assertObjectHasAttribute('title', $results->errors);
        $this->assertInternalType('array', $results->errors->title);
        $this->assertEquals('The title field is required.', $results->errors->title[0]);
    }
}
