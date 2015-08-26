<?php

$this->set('version', '1.0');
$this->set('links', '{}');
$this->set('meta', '{}');

$this->set('entities', $this->each([ $category ], function ($section, $category) {

    $section->set($section->partial('partials/category', [ 'category' => $category ]));
}));

$this->set('linked', '{}');
