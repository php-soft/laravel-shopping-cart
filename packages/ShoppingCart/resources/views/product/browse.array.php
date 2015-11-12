<?php

$this->set('version', '1.0');
$this->set('links', $this->helper('phpsoft.shoppingcart::helpers.links', $products['data']));
$this->set('meta', function ($section) use ($products) {
    $section->set('offset', $products['offset']);
    $section->set('limit', $products['limit']);
    $section->set('total', $products['total']);
});

$this->set('entities', $this->each($products['data'], function ($section, $product) {

    $section->set($section->partial('phpsoft.shoppingcart::partials/product', [ 'product' => $product ]));
}));

$this->set('linked', '{}');
