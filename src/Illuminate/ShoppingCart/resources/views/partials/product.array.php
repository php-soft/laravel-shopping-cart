<?php

$this->set('title', $product->title);
$this->set('alias', $product->alias);
$this->set('image', $product->image);
$this->set('description', $product->description);
$this->set('price', $product->price);
$this->set('galeries', $this->each((array)json_decode($product->galeries, true), function ($section, $item) {

    $section->set($item);
}));
$this->set('created_at', $product->created_at);
