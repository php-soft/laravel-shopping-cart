<?php

use PhpSoft\Illuminate\ShoppingCart\Models\Category;

use Illuminate\Foundation\Testing\WithoutMiddleware;

class CategoryControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testCreateNotAuthAndPermission()
    {
        $res = $this->call('POST', '/categories');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('POST', '/categories');
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testCreateValidateFailure()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('POST', '/categories', [
            'alias' => 'This is invalid alias',
            'parent_id' => 'invalid',
            'order' => 'invalid',
            'status' => 'invalid',
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('name', $results->errors);
        $this->assertEquals('The name field is required.', $results->errors->name[0]);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertInternalType('array', $results->errors->parent_id);
        $this->assertEquals('The parent id must be a number.', $results->errors->parent_id[0]);
        $this->assertInternalType('array', $results->errors->order);
        $this->assertEquals('The order must be a number.', $results->errors->order[0]);
        $this->assertInternalType('array', $results->errors->status);
        $this->assertEquals('The status must be a number.', $results->errors->status[0]);
    }

    public function testCreateSuccess()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
        ]);

        $this->assertEquals(201, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals('Example Category', $results->entities[0]->name);
        $this->assertEquals(null, $results->entities[0]->description);
        $this->assertEquals(null, $results->entities[0]->image);
        $this->assertEquals(0, $results->entities[0]->parent_id);
        $this->assertEquals(0, $results->entities[0]->order);
        $this->assertEquals(1, $results->entities[0]->status);
    }

    public function testCreateExistsAlias()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $category = factory(Category::class)->create();

        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'alias' => $category->alias,
        ]);

        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('alias', $results->errors);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testCreateWithParentIdNotExists()
    {
        $user = Mockery::mock('user');
        $user->shouldReceive('can')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'parent_id' => 0,
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('The selected parent id is invalid.', $results->errors->parent_id[0]);
    }

    public function testCreateWithParentIdExists()
    {
        $categoryParent = factory(Category::class)->create();

        $user = Mockery::mock('user');
        $user->shouldReceive('can')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'parent_id' => $categoryParent->id,
        ]);

        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($categoryParent->id, $results->entities[0]->parent_id);
    }

    public function testReadNotFound()
    {
        $res = $this->call('GET', '/categories/0');

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testReadFound()
    {
        $category = factory(Category::class)->create();

        $res = $this->call('GET', '/categories/' . $category->id);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($category->name, $results->entities[0]->name);
        $this->assertEquals($category->alias, $results->entities[0]->alias);
        $this->assertEquals($category->description, $results->entities[0]->description);
        $this->assertEquals($category->image, $results->entities[0]->image);
    }

    public function testUpdateNotAuthAndPermission()
    {
        $res = $this->call('PUT', '/categories/0');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PUT', '/categories/0');
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testUpdateNotExists()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/categories/0');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testUpdateValidateFailure()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/categories/' . $category->id, [
            'alias' => 'Invalid Alias',
            'parent_id' => 'invalid',
            'order' => 'invalid',
            'status' => 'invalid',
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertEquals('The parent id must be a number.', $results->errors->parent_id[0]);
        $this->assertEquals('The order must be a number.', $results->errors->order[0]);
        $this->assertEquals('The status must be a number.', $results->errors->status[0]);
    }

    public function testUpdateNothingChange()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/categories/' . $category->id);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($category->name, $results->entities[0]->name);
        $this->assertEquals($category->alias, $results->entities[0]->alias);
        $this->assertEquals($category->description, $results->entities[0]->description);
        $this->assertEquals($category->parent_id, $results->entities[0]->parent_id);
        $this->assertEquals($category->order, $results->entities[0]->order);
        $this->assertEquals($category->status, $results->entities[0]->status);
    }

    public function testUpdateWithNewInformation()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/categories/' . $category->id, [
            'name' => 'New Name',
            'alias' => 'new-alias',
            'description' => 'New description',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('New Name', $results->entities[0]->name);
        $this->assertEquals('new-alias', $results->entities[0]->alias);
        $this->assertEquals('New description', $results->entities[0]->description);

        // change keep current alias
        $res = $this->call('PUT', '/categories/' . $category->id, [
            'name' => 'New Name',
            'alias' => 'new-alias',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('new-alias', $results->entities[0]->alias);
    }

    public function testUpdateWithExistsAlias()
    {
        $category = factory(Category::class)->create();
        $otherProduct = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PUT', '/categories/' . $category->id, [
            'name' => 'New Title',
            'alias' => $otherProduct->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testUpdateWithParentIdNotExists()
    {
        $category = factory(Category::class)->create();

        $user = Mockery::mock('user');
        $user->shouldReceive('can')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $res = $this->call('PUT', '/categories/' . $category->id, [
            'name' => 'Example Category',
            'parent_id' => 0,
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('The selected parent id is invalid.', $results->errors->parent_id[0]);
    }

    public function testUpdateWithParentIdExists()
    {
        $category = factory(Category::class)->create();
        $categoryParent = factory(Category::class)->create();

        $user = Mockery::mock('user');
        $user->shouldReceive('can')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $res = $this->call('PUT', '/categories/' . $category->id, [
            'name' => 'Example Category',
            'parent_id' => $categoryParent->id,
        ]);

        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($categoryParent->id, $results->entities[0]->parent_id);
    }

    public function testDeleteNotAuthAndPermission()
    {
        $res = $this->call('DELETE', '/categories/0');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('DELETE', '/categories/0');
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testDeleteNotFound()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('DELETE', '/categories/0');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testDeleteSuccess()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('DELETE', "/categories/{$category->id}");
        $this->assertEquals(204, $res->getStatusCode());

        $exists = Category::find($category->id);
        $this->assertNull($exists);
    }

    public function testBrowseNotFound()
    {
        $res = $this->call('GET', '/categories');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseFound()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        $res = $this->call('GET', '/categories');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(count($categories), count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }
    }

    public function testBrowseWithPagination()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        // 5 items first
        $res = $this->call('GET', '/categories?limit=5');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        // 5 items next
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }
}
