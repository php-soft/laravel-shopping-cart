<?php

use PhpSoft\Illuminate\ShoppingCart\Models\Product;

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

    public function testCreateValidatePrice()
    {
        $res = $this->call('POST', '/products', [
            'title' => 'Example Product',
            'price' => 'invalid',
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('price', $results->errors);
        $this->assertInternalType('array', $results->errors->price);
        $this->assertEquals('The price must be a number.', $results->errors->price[0]);
    }

    public function testCreateSuccess()
    {
        $res = $this->call('POST', '/products', [
            'title' => 'Example Product',
        ]);

        $this->assertEquals(201, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals('Example Product', $results->entities[0]->title);
        $this->assertEquals(null, $results->entities[0]->description);
        $this->assertEquals(null, $results->entities[0]->image);
        $this->assertEquals([], $results->entities[0]->galleries);
    }

    public function testCreateExistsAlias()
    {
        $product = factory(Product::class)->create();

        $res = $this->call('POST', '/products', [
            'title' => 'Example Product',
            'alias' => $product->alias,
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('alias', $results->errors);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testCreateWithGalleries()
    {
        $galleries = [
            \Webpatser\Uuid\Uuid::generate(4) . '.jpg',
            \Webpatser\Uuid\Uuid::generate(4) . '.jpg',
            \Webpatser\Uuid\Uuid::generate(4) . '.jpg',
        ];
        $res = $this->call('POST', '/products', [
            'title' => 'Example Product',
            'galleries' => $galleries,
        ]);

        $this->assertEquals(201, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertInternalType('array', $results->entities[0]->galleries);
        $this->assertEquals($galleries[0], $results->entities[0]->galleries[0]);
        $this->assertEquals($galleries[1], $results->entities[0]->galleries[1]);
        $this->assertEquals($galleries[2], $results->entities[0]->galleries[2]);
    }

    public function testReadNotFound()
    {
        $res = $this->call('GET', '/products/0');

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testReadFound()
    {
        $product = factory(Product::class)->create();

        $res = $this->call('GET', '/products/' . $product->id);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($product->title, $results->entities[0]->title);
        $this->assertEquals($product->alias, $results->entities[0]->alias);
        $this->assertEquals($product->description, $results->entities[0]->description);
        $this->assertEquals($product->image, $results->entities[0]->image);
    }

    public function testUpdateProductNotExists()
    {
        $res = $this->call('PUT', '/products/0');

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testUpdateValidateFailure()
    {
        $product = factory(Product::class)->create();

        // no permission
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('PUT', '/products/' . $product->id);
        $this->assertEquals(403, $res->getStatusCode());

        // has permission
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        // no title
        $res = $this->call('PUT', '/products/' . $product->id, [
            'alias' => 'Invalid Alias',
            'price' => 'invalid',
            'galleries' => 'invalid',
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertEquals('The price must be a number.', $results->errors->price[0]);
        $this->assertEquals('The galleries must be an array.', $results->errors->galleries[0]);

        // passing without change
        $res = $this->call('PUT', '/products/' . $product->id);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($product->title, $results->entities[0]->title);
        $this->assertEquals($product->alias, $results->entities[0]->alias);
        $this->assertEquals($product->description, $results->entities[0]->description);
        $this->assertEquals($product->price, $results->entities[0]->price);
        $this->assertEquals($product->image, $results->entities[0]->image);

        // passing with change
        $res = $this->call('PUT', '/products/' . $product->id, [
            'title' => 'New Title',
            'alias' => 'new-alias',
            'description' => 'New description',
            'price' => 123456,
            'image' => 'image.jpg',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('New Title', $results->entities[0]->title);
        $this->assertEquals('new-alias', $results->entities[0]->alias);
        $this->assertEquals('New description', $results->entities[0]->description);
        $this->assertEquals(123456, $results->entities[0]->price);
        $this->assertEquals('image.jpg', $results->entities[0]->image);

        // change keep current alias
        $res = $this->call('PUT', '/products/' . $product->id, [
            'title' => 'New Title',
            'alias' => 'new-alias',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('new-alias', $results->entities[0]->alias);

        // change with exists alias
        $product2 = factory(Product::class)->create();
        $res = $this->call('PUT', '/products/' . $product->id, [
            'title' => 'New Title',
            'alias' => $product2->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }
}
