<?php

namespace PhpSoft\Illuminate\ShoppingCart\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Http\Requests;

use PhpSoft\Illuminate\ShoppingCart\Models\Product;
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
        //
    }

    /**
     * Create product action
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
        if (!$this->checkPermission('create-product')) {
            return response()->json(null, 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_products',
            'image' => 'string',
            'description' => 'string',
            'price' => 'numeric',
            'galleries' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $product = Product::create($request->all());

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
        $product = Product::find($id);

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
        // check authenticate
        if (!$this->checkAuth()) {
            return response()->json(null, 401);
        }

        $product = Product::find($id);

        // check exists
        if (empty($product)) {
            return response()->json(null, 404);
        }

        // check permission
        if (!$this->checkPermission('edit-product')) {
            return response()->json(null, 403);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'alias' => 'regex:/^[a-z0-9\-]+/|unique:shop_products,alias,' . $product->id,
            'image' => 'string',
            'description' => 'string',
            'price' => 'numeric',
            'galleries' => 'array',
        ]);
        if ($validator->fails()) {
            return response()->json(arrayView('errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // update
        $product = $product->update($request->all());

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
        //
    }
}
