<?php

$this->extract($category, [
    'id',
    'name',
    'alias',
    'image',
    'description',
    'parent_id',
    'order',
    'status',
    'created_at',
]);
$this->set('parentId', $category->parent_id);
$this->set('createdAt', date('c', strtotime($category->created_at)));
