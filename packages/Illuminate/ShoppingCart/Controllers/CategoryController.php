<?php

namespace PhpSoft\Illuminate\ShoppingCart\Controllers;

use Input;
use Validator;
use Illuminate\Http\Request;

use App\Http\Requests;

use PhpSoft\Illuminate\ShoppingCart\Models\Category;
use PhpSoft\Illuminate\ShoppingCart\Controllers\Controller;

/**
 * Category REST
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $categories = Category::browse([
            'order'     => [ 'id' => 'desc' ],
            'limit'     => (int)Input::get('limit') ? (int)Input::get('limit') : 25,
            'cursor'    => Input::get('cursor'),
        ]);

        return response()->json(arrayView('category/browse', [
            'categories' => $categories,
        ]), 200);
    }

    /**
     * Create resource action
     * 
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        // check authenticate
        if (!$this->checkAuth()) {
            return response()->json(null, 401);
        }

        // check permission
        if (!$this->checkPermission('create-category')) {
            return response()->json(null, 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_categories',
            'image' => 'string',
            'description' => 'string',
            'parent_id' => 'numeric|exists:shop_categories,id',
            'order' => 'numeric',
            'status' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $category = Category::create($request->all());

        return response()->json(arrayView('category/read', [
            'category' => $category
        ]), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('category/read', [
            'category' => $category
        ]), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int     $id
     * @param  Request $request
     * @return Response
     */
    public function update($id, Request $request)
    {
        // check authenticate
        if (!$this->checkAuth()) {
            return response()->json(null, 401);
        }

        // check permission
        if (!$this->checkPermission('edit-category')) {
            return response()->json(null, 403);
        }

        $category = Category::find($id);

        // check exists
        if (empty($category)) {
            return response()->json(null, 404);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_categories,alias,' . $category->id,
            'image' => 'string',
            'description' => 'string',
            'parent_id' => 'numeric|exists:shop_categories,id',
            'order' => 'numeric',
            'status' => 'numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(arrayView('errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // update
        $category = $category->update($request->all());

        // respond
        return response()->json(arrayView('category/read', [
            'category' => $category
        ]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // check authenticate
        if (!$this->checkAuth()) {
            return response()->json(null, 401);
        }

        // check permission
        if (!$this->checkPermission('delete-category')) {
            return response()->json(null, 403);
        }

        // retrieve category
        $category = Category::find($id);

        // check exists
        if (empty($category)) {
            return response()->json(null, 404);
        }

        if (!$category->delete()) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }
}
