<?php

// Makes sense to run this stuff every minute rather than live, no?

$container = require __DIR__ . '/../container.php';

$container->get('users-projector')();
