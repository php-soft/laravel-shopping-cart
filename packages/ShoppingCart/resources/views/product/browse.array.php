<?php

$this->set('version', '1.0');
$this->set('links', $this->helper('phpsoft.shoppingcart::helpers.links', $products));
$this->set('meta', '{}');

$this->set('entities', $this->each($products, function ($section, $product) {

    $section->set($section->partial('phpsoft.shoppingcart::partials/product', [ 'product' => $product ]));
}));

$this->set('linked', '{}');
