<?php

$this->set('version', '1.0');
$this->set('links', $this->helper('links', $categories));
$this->set('meta', '{}');

$this->set('entities', $this->each($categories, function ($section, $category) {

    $section->set($section->partial('partials/category', [ 'category' => $category ]));
}));

$this->set('linked', '{}');
