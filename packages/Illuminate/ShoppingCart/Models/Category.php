<?php

namespace PhpSoft\Illuminate\ShoppingCart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Exception;

use Webpatser\Uuid\Uuid;
use Illuminate\Support\Str;

class Category extends Model
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name', 'alias', 'image', 'description', 'parent_id', 'order', 'status'];

    /**
     * Create Category
     * 
     * @param  array  $attributes        Attributes
     * @return PhpSoft\Illuminate\ShoppingCart\Model\Product
     */
    public static function create(array $attributes = [])
    {
        if (empty($attributes['alias'])) {
            $attributes['alias'] = Str::slug($attributes['name'])
                .'-'.Uuid::generate(4);
        }

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
            $name = $this->name;
            if (isset($attributes['name'])) {
                $name = $attributes['name'];
            }
            $attributes['alias'] = Str::slug($name)
                .'-'.Uuid::generate(4);
        }

        if (!parent::update($attributes)) {
            throw new Exception('Cannot update category.');
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
}
