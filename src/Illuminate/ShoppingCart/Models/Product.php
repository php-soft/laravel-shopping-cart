<?php

namespace PhpSoft\Illuminate\ShoppingCart\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_products';

    /**
     * Create Product
     * 
     * @param  array  $attributes        Attributes
     * @return PhpSoft\Illuminate\ShoppingCart\Model\Product
     */
    public static function create(array $attributes = [])
    {
        return parent::create($attributes);
    }
}
