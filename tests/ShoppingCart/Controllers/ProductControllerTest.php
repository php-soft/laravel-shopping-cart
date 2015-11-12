<?php

use PhpSoft\ShoppingCart\Models\Product;
use PhpSoft\ShoppingCart\Models\Category;

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
            'categories' => 'invalid',
            'attributes' => 'invalid',
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('name', $results->errors);
        $this->assertEquals('The name field is required.', $results->errors->name[0]);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertInternalType('array', $results->errors->price);
        $this->assertEquals('The price must be a number.', $results->errors->price[0]);
        $this->assertInternalType('array', $results->errors->galleries);
        $this->assertEquals('The galleries must be an array.', $results->errors->galleries[0]);
        $this->assertEquals('The categories must be an array.', $results->errors->categories[0]);
        $this->assertEquals('The attributes must be an array.', $results->errors->attributes[0]);
    }

    public function testCreateSuccess()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('POST', '/products', [
            'name' => 'Example Product',
        ]);

        $this->assertEquals(201, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals('Example Product', $results->entities[0]->name);
        $this->assertEquals(null, $results->entities[0]->description);
        $this->assertEquals(null, $results->entities[0]->image);
        $this->assertEquals([], $results->entities[0]->galleries);
        $this->assertEquals([], $results->entities[0]->attributes);
    }

    public function testCreateExistsAlias()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $product = factory(Product::class)->create();

        $res = $this->call('POST', '/products', [
            'name' => 'Example Product',
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
            'name' => 'Example Product',
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

    public function testCreateWithCategories()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $category1 = factory(Category::class)->create();
        $category2 = factory(Category::class)->create();

        $categories = [
            $category1->id,
            $category2->id,
        ];
        $res = $this->call('POST', '/products', [
            'name' => 'Example Product',
            'categories' => $categories,
        ]);

        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $productId = $results->entities[0]->id;

        $product = Product::find($productId);
        $this->assertEquals(2, count($product->categories));
    }

    public function testCreateWithAttributes()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $attributes = [
            [ 'name' => 'model', 'value' => 'MD01' ],
            [ 'name' => 'ship_fee', 'value' => 'Free' ],
        ];
        $res = $this->call('POST', '/products', [
            'name' => 'Example Product',
            'attributes' => $attributes,
        ]);

        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());

        $product = $results->entities[0];
        $this->assertEquals(2, count($product->attributes));
        $this->assertEquals('model', $product->attributes[0]->name);
        $this->assertEquals('MD01', $product->attributes[0]->value);
        $this->assertEquals('ship_fee', $product->attributes[1]->name);
        $this->assertEquals('Free', $product->attributes[1]->value);
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
        $this->assertEquals($product->name, $results->entities[0]->name);
        $this->assertEquals($product->alias, $results->entities[0]->alias);
        $this->assertEquals($product->description, $results->entities[0]->description);
        $this->assertEquals($product->image, $results->entities[0]->image);
    }

    public function testReadFoundWithCategory()
    {
        $category1 = factory(Category::class)->create();
        $category2 = factory(Category::class)->create();
        $product = factory(Product::class)->create();
        $product->categories()->sync([ $category1->id, $category2->id ]);

        $res = $this->call('GET', '/products/' . $product->id);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($product->name, $results->entities[0]->name);
        $this->assertEquals($product->alias, $results->entities[0]->alias);
        $this->assertEquals($product->description, $results->entities[0]->description);
        $this->assertEquals($product->image, $results->entities[0]->image);

        $this->assertEquals($category1->id, $results->entities[0]->categories[0]);
        $this->assertEquals($category2->id, $results->entities[0]->categories[1]);
    }

    public function testReadWithAlias()
    {
        $product = factory(Product::class)->create();

        $res = $this->call('GET', '/products/' . $product->alias);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($product->name, $results->entities[0]->name);
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
            'categories' => 'invalid',
            'attributes' => 'invalid',
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertEquals('The price must be a number.', $results->errors->price[0]);
        $this->assertEquals('The galleries must be an array.', $results->errors->galleries[0]);
        $this->assertEquals('The categories must be an array.', $results->errors->categories[0]);
        $this->assertEquals('The attributes must be an array.', $results->errors->attributes[0]);
    }

    public function testUpdateNothingChange()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/products/' . $product->id);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($product->name, $results->entities[0]->name);
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
            'name' => 'New name',
            'alias' => 'new-alias',
            'description' => 'New description',
            'price' => 123456,
            'image' => 'image.jpg',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('New name', $results->entities[0]->name);
        $this->assertEquals('new-alias', $results->entities[0]->alias);
        $this->assertEquals('New description', $results->entities[0]->description);
        $this->assertEquals(123456, $results->entities[0]->price);
        $this->assertEquals('image.jpg', $results->entities[0]->image);

        // change keep current alias
        $res = $this->call('PUT', '/products/' . $product->id, [
            'name' => 'New name',
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
            'name' => 'New name',
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
            'name' => 'New name',
            'alias' => $otherProduct->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testUpdateWithCategories()
    {
        $product = factory(Product::class)->create();
        $category1 = factory(Category::class)->create();
        $category2 = factory(Category::class)->create();
        $product->categories()->attach($category1->id);

        // before update: have one category
        $this->assertEquals(1, count($product->categories));

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $categories = [
            $category1->id,
            $category2->id,
        ];
        $res = $this->call('PUT', '/products/' . $product->id, [
            'name' => 'New name',
            'categories' => $categories,
        ]);

        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());

        // after update: have two categories
        $product = Product::find($product->id);
        $this->assertEquals(2, count($product->categories));
    }

    public function testUpdateWithAttributes()
    {
        $product = factory(Product::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $attributes = [
            [ 'name' => 'model', 'value' => 'MD01' ],
            [ 'name' => 'ship_fee', 'value' => 'Free' ],
        ];
        $res = $this->call('PUT', '/products/' . $product->id, [
            'name' => 'New name',
            'attributes' => $attributes,
        ]);

        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());

        $product = $results->entities[0];
        $this->assertEquals(2, count($product->attributes));
        $this->assertEquals('model', $product->attributes[0]->name);
        $this->assertEquals('MD01', $product->attributes[0]->value);
        $this->assertEquals('ship_fee', $product->attributes[1]->name);
        $this->assertEquals('Free', $product->attributes[1]->value);
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

    public function testBrowseWithScroll()
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
        $res = $this->call('GET', '/products?limit=5&page=2');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($products[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $res = $this->call('GET', '/products?limit=5&page=3');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseByCategoryWithNotExistsCategory()
    {
        $res = $this->call('GET', '/categories/0/products');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testBrowseByCategoryNotFound()
    {
        $category = factory(Category::class)->create();

        $res = $this->call('GET', '/categories/' . $category->id . '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));

        $res = $this->call('GET', '/categories/' . $category->alias . '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseByCategoryWithPagination()
    {
        $category = factory(Category::class)->create();

        $products = [];
        for ($i = 0; $i < 10; ++$i) {
            $product = factory(Product::class)->create();
            $product->categories()->sync([$category->id]);
            $products[] = $product;
        }
        $product = factory(Product::class)->create();

        // 5 items first
        $res = $this->call('GET', '/categories/' . $category->id . '/products?limit=5');
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

    public function testBrowseWithSort()
    {
        $products = [];
        for ($i = 0; $i < 10; ++$i) {
            $products[] = factory(Product::class)->create([
                'name' => 9 - $i,
            ]);
        }

        $res = $this->call('GET', '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($products[9 - $i]->id, $results->entities[$i]->id);
        }

        $res = $this->call('GET', '/products?sort=name');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($products[$i]->id, $results->entities[$i]->id);
        }

        $res = $this->call('GET', '/products?sort=name&direction=asc');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($products[9 - $i]->id, $results->entities[$i]->id);
        }
    }

    public function testBrowseWithFilter()
    {
        $products = [];
        for ($i = 0; $i < 10; ++$i) {
            $products[] = factory(Product::class)->create([
                'name' => 'Test' . $i,
            ]);
        }

        $res = $this->call('GET', '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));

        $res = $this->call('GET', '/products?name=Test0');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(1, $results->meta->total);
        $this->assertEquals(1, count($results->entities));

        $res = $this->call('GET', '/products?name=Test%');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));
    }

    public function testBrowseWithCategoryAndFilter()
    {
        $category = factory(Category::class)->create();

        $products = [];
        for ($i = 0; $i < 10; ++$i) {
            $product = factory(Product::class)->create([
                'name' => 'Test' . $i,
            ]);
            $product->categories()->sync([$category->id]);
            $products[] = $product;
        }
        $product = factory(Product::class)->create();

        $res = $this->call('GET', '/categories/' . $category->id . '/products');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));

        $res = $this->call('GET', '/categories/' . $category->id . '/products?name=Test0');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(1, $results->meta->total);
        $this->assertEquals(1, count($results->entities));

        $res = $this->call('GET', '/categories/' . $category->id . '/products?name=Test%');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));
    }
}
