<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class ProductControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testCreateValidateTitle()
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

    public function testCreateValidateAlias()
    {
        $res = $this->call('POST', '/products', [
            'title' => 'Example Product',
            'alias' => 'This is invalid alias',
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('alias', $results->errors);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
    }

    public function testCreateSuccess()
    {
        $res = $this->call('POST', '/products', [
            'title' => 'Example Product',
        ]);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals('Example Product', $results->entities[0]->title);
        $this->assertEquals(null, $results->entities[0]->description);
        $this->assertEquals(null, $results->entities[0]->image);
        $this->assertEquals([], $results->entities[0]->galeries);
    }
}
