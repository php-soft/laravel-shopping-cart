<?php

namespace PhpSoft\Illuminate\ShoppingCart\Controllers;

use Input;
use Validator;
use Illuminate\Http\Request;

use App\Http\Requests;

use PhpSoft\Illuminate\ShoppingCart\Models\Product;
use PhpSoft\Illuminate\ShoppingCart\Models\Category;
use PhpSoft\Illuminate\ShoppingCart\Controllers\Controller;

/**
 * Product REST
 */
class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $products = Product::browse([
            'order'     => [ 'id' => 'desc' ],
            'limit'     => (int)Input::get('limit') ? (int)Input::get('limit') : 25,
            'cursor'    => Input::get('cursor'),
        ]);

        return response()->json(arrayView('product/browse', [
            'products' => $products,
        ]), 200);
    }

    /**
     * Create product action
     * 
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_products',
            'image' => 'string',
            'description' => 'string',
            'price' => 'numeric',
            'galleries' => 'array',
            'categories' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $product = Product::create($request->all());

        // create link between categories and product
        if ($request->categories) {
            $product->categories()->sync($request->categories);
        }

        return response()->json(arrayView('product/read', [
            'product' => $product
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
        $product = Product::findByIdOrAlias($id);

        if (empty($product)) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('product/read', [
            'product' => $product
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
        $product = Product::find($id);

        // check exists
        if (empty($product)) {
            return response()->json(null, 404);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_products,alias,' . $product->id,
            'image' => 'string',
            'description' => 'string',
            'price' => 'numeric',
            'galleries' => 'array',
            'categories' => 'array',
        ]);
        if ($validator->fails()) {
            return response()->json(arrayView('errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // update
        $product = $product->update($request->all());

        // create/update link between categories and product
        if ($request->categories) {
            $product->categories()->sync($request->categories);
        }

        // respond
        return response()->json(arrayView('product/read', [
            'product' => $product
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
        // retrieve product
        $product = Product::find($id);

        // check exists
        if (empty($product)) {
            return response()->json(null, 404);
        }

        if (!$product->delete()) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }
}
