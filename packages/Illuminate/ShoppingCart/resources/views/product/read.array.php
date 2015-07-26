<?php

$this->set('version', '1.0');
$this->set('links', '{}');
$this->set('meta', '{}');

$this->set('entities', $this->each([ $product ], function ($section, $product) {

    $section->set($section->partial('partials/product', [ 'product' => $product ]));
}));

$this->set('linked', '{}');
