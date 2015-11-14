<?php

namespace PhpSoft\ShoppingCart\Controllers;

use Input;
use Validator;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Product REST
 */
class ProductController extends Controller
{
    private $categoryModel = '';
    private $productModel = '';

    /**
     * Construct controller
     */
    public function __construct()
    {
        $this->categoryModel = config('phpsoft.shoppingcart.categoryModel');
        $this->productModel = config('phpsoft.shoppingcart.productModel');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, $categoryId = null)
    {
        $categoryModel = $this->categoryModel;
        $productModel = $this->productModel;

        $options = [
            'order'     => [ Input::get('sort', 'shop_products.id') => Input::get('direction', 'desc') ],
            'limit'     => ($limit = (int)Input::get('limit', 25)),
            'offset'    => (Input::get('page', 1) - 1) * $limit,
            'cursor'    => Input::get('cursor'),
            'filters'   => $request->all(),
        ];

        if ($categoryId != null) {
            $category = $categoryModel::findByIdOrAlias($categoryId);
            if (empty($category)) {
                return response()->json(null, 404);
            }

            $products = $productModel::browseByCategory($category, $options);
        } else {
            $products = $productModel::browse($options);
        }

        return response()->json(arrayView('phpsoft.shoppingcart::product/browse', [
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
            'name'          => 'required',
            'alias'         => 'regex:/^[a-z0-9\-]+/|unique:shop_products',
            'image'         => 'string',
            'description'   => 'string',
            'price'         => 'numeric',
            'galleries'     => 'array',
            'categories'    => 'array',
            'attributes'    => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.shoppingcart::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $productModel = $this->productModel;
        $product = $productModel::create($request->all());

        // create link between categories and product
        if ($request->categories) {
            $product->categories()->sync($request->categories);
        }

        return response()->json(arrayView('phpsoft.shoppingcart::product/read', [
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
        $productModel = $this->productModel;
        $product = $productModel::findByIdOrAlias($id);

        if (empty($product)) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('phpsoft.shoppingcart::product/read', [
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
        $productModel = $this->productModel;
        $product = $productModel::find($id);

        // check exists
        if (empty($product)) {
            return response()->json(null, 404);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'name'          => 'string',
            'alias'         => 'regex:/^[a-z0-9\-]+/|unique:shop_products,alias,' . $product->id,
            'image'         => 'string',
            'description'   => 'string',
            'price'         => 'numeric',
            'galleries'     => 'array',
            'categories'    => 'array',
            'attributes'    => 'array',
        ]);
        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.shoppingcart::errors/validation', [
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
        return response()->json(arrayView('phpsoft.shoppingcart::product/read', [
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
        $productModel = $this->productModel;

        // retrieve product
        $product = $productModel::find($id);

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
