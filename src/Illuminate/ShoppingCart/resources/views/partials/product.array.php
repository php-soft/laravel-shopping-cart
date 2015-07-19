<?php

$this->set('id', $product->id);
$this->set('title', $product->title);
$this->set('alias', $product->alias);
$this->set('image', $product->image);
$this->set('description', $product->description);
$this->set('price', $product->price);
$this->set('galleries', $this->each(json_decode($product->galleries), function ($section, $item) {

    $section->set($item);
}));
$this->set('created_at', $product->created_at);
