<?php

$this->set('version', '1.0');
$this->set('links', $this->helper('links', $products));
$this->set('meta', '{}');

$this->set('entities', $this->each($products, function ($section, $product) {

    $section->set($section->partial('partials/product', [ 'product' => $product ]));
}));

$this->set('linked', '{}');
