<?php

use PhpSoft\Illuminate\ShoppingCart\Models\Product;

class ProductControllerTest extends TestCase
{
    public function testCreateNotAuthAndPermission()
    {
        $res = $this->call('POST', '/products');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('POST', '/products');
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testCreateValidateFailure()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('POST', '/products', [
            'alias' => 'This is invalid alias',
            'price' => 'invalid',
            'galleries' => 'invalid',
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('title', $results->errors);
        $this->assertEquals('The title field is required.', $results->errors->title[0]);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertInternalType('array', $results->errors->price);
        $this->assertEquals('The price must be a number.', $results->errors->price[0]);
        $this->assertInternalType('array', $results->errors->galleries);
        $this->assertEquals('The galleries must be an array.', $results->errors->galleries[0]);
    }

    public function testCreateSuccess()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

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
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

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
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

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

        $res = $this->call('GET', '/products/not-found');
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

    public function testReadWithAlias()
    {
        $product = factory(Product::class)->create();

        $res = $this->call('GET', '/products/' . $product->alias);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($product->title, $results->entities[0]->title);
        $this->assertEquals($product->alias, $results->entities[0]->alias);
        $this->assertEquals($product->description, $results->entities[0]->description);
        $this->assertEquals($product->image, $results->entities[0]->image);
    }

    public function testUpdateNotAuthAndPermission()
    {
        $res = $this->call('PUT', '/products/0');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PUT', '/products/0');
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testUpdateNotExists()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/products/0');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testUpdateValidateFailure()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

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
    }

    public function testUpdateNothingChange()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/products/' . $product->id);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($product->title, $results->entities[0]->title);
        $this->assertEquals($product->alias, $results->entities[0]->alias);
        $this->assertEquals($product->description, $results->entities[0]->description);
        $this->assertEquals($product->price, $results->entities[0]->price);
        $this->assertEquals($product->image, $results->entities[0]->image);
    }

    public function testUpdateWithNewInformation()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

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
    }

    public function testUpdateWithBlankAlias()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/products/' . $product->id, [
            'title' => 'New Title',
            'alias' => '',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertNotEquals($product->alias, $results->entities[0]->alias);
    }

    public function testUpdateWithExistsAlias()
    {
        $product = factory(Product::class)->create();
        $otherProduct = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/products/' . $product->id, [
            'title' => 'New Title',
            'alias' => $otherProduct->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testDeleteNotAuthAndPermission()
    {
        $res = $this->call('DELETE', '/products/0');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('DELETE', '/products/0');
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testDeleteNotFound()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('DELETE', '/products/0');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testDeleteSuccess()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('DELETE', "/products/{$product->id}");
        $this->assertEquals(204, $res->getStatusCode());

        $exists = Product::find($product->id);
        $this->assertNull($exists);
    }

    public function testBrowseNotFound()
    {
        $res = $this->call('GET', '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseFound()
    {
        $products = [];
        for ($i = 0; $i < 10; ++$i) {
            $products[] = factory(Product::class)->create();
        }

        $res = $this->call('GET', '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(count($products), count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($products[9 - $i]->id, $results->entities[$i]->id);
        }
    }

    public function testBrowseWithPagination()
    {
        $products = [];
        for ($i = 0; $i < 10; ++$i) {
            $products[] = factory(Product::class)->create();
        }

        // 5 items first
        $res = $this->call('GET', '/products?limit=5');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($products[9 - $i]->id, $results->entities[$i]->id);
        }

        // 5 items next
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($products[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }
}
