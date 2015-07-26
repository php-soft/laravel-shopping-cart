<?php

$this->set('version', '1.0');
$this->set('status', 'error');
$this->set('type', 'validation');
$this->set('errors', $errors);
$this->set('first_message', $errors->first());
