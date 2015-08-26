<?php

namespace PhpSoft\ShoppingCart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Exception;

use Webpatser\Uuid\Uuid;
use Illuminate\Support\Str;

class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'title', 'alias', 'image', 'description', 'price', 'galleries', 'attributes'];

    /**
     * Get the categories for the product.
     */
    public function categories()
    {
        return $this->belongsToMany('PhpSoft\ShoppingCart\Models\Category', 'shop_category_product');
    }

    /**
     * Create Product
     * 
     * @param  array  $attributes        Attributes
     * @return PhpSoft\ShoppingCart\Model\Product
     */
    public static function create(array $attributes = [])
    {
        if (empty($attributes['alias'])) {
            $attributes['alias'] = Str::slug($attributes['title'])
                .'-'.Uuid::generate(4);
        }

        if (empty($attributes['galleries'])) {
            $attributes['galleries'] = [];
        }
        $attributes['galleries'] = json_encode($attributes['galleries']);

        if (empty($attributes['attributes'])) {
            $attributes['attributes'] = [];
        }
        $attributes['attributes'] = json_encode($attributes['attributes']);

        return parent::create($attributes)->fresh();
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @return bool|int
     */
    public function update(array $attributes = [])
    {
        if (isset($attributes['alias']) && empty($attributes['alias'])) {
            $title = $this->title;
            if (isset($attributes['title'])) {
                $title = $attributes['title'];
            }
            $attributes['alias'] = Str::slug($title)
                .'-'.Uuid::generate(4);
        }

        if (empty($attributes['galleries'])) {
            $attributes['galleries'] = [];
        }
        $attributes['galleries'] = json_encode($attributes['galleries']);

        if (empty($attributes['attributes'])) {
            $attributes['attributes'] = [];
        }
        $attributes['attributes'] = json_encode($attributes['attributes']);

        if (!parent::update($attributes)) {
            throw new Exception('Cannot update product.');
        }

        return $this;
    }

    /**
     * List
     * 
     * @param  array  $options
     * @return array
     */
    public static function browse($options = [])
    {
        if (empty($options)) {
            return parent::all();
        }

        if (!empty($options['order'])) {
            foreach ($options['order'] as $field => $direction) {
                $find = parent::orderBy($field, $direction);
            }
        }

        if (!empty($options['limit'])) {
            $find = $find->take($options['limit']);
        }

        if (!empty($options['cursor'])) {
            $find = $find->where('id', '<', $options['cursor']);
        }

        return $find->get();
    }

    /**
     * Find products by category
     *
     * @param  Category $category
     * @param  array    $options
     * @return array
     */
    public static function browseByCategory($category, $options = [])
    {
        $find = $category->products();

        if (!empty($options['order'])) {
            foreach ($options['order'] as $field => $direction) {
                $find = $find->orderBy($field, $direction);
            }
        }

        if (!empty($options['limit'])) {
            $find = $find->take($options['limit']);
        }

        if (!empty($options['cursor'])) {
            $find = $find->where('shop_products.id', '<', $options['cursor']);
        }

        return $find->get();
    }

    /**
     * Find by id or alias
     *
     * @param  string $idOrAlias
     * @return Product
     */
    public static function findByIdOrAlias($idOrAlias)
    {
        $product = parent::find($idOrAlias);

        if ($product) {
            return $product;
        }

        return parent::where('alias', $idOrAlias)->first();
    }
}
