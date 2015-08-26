<?php

$this->set('id', $product->id);
$this->set('title', $product->title);
$this->set('alias', $product->alias);
$this->set('image', $product->image);
$this->set('description', $product->description);
$this->set('price', $product->price);
$this->set('galleries', $this->each(empty($product->galleries) ? [] : json_decode($product->galleries), function ($section, $item) {

    $section->set($item);
}));
$this->set('attributes', $this->each(empty($product->attributes) ? [] : json_decode($product->attributes), function ($section, $item) {

    $section->set($item);
}));
$this->set('categories', $this->each($product->categories()->get(), function ($section, $category) {

    $section->set($category->id);
}));
$this->set('created_at', $product->created_at);
