<?php

session_start();

require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Model.php';

$router = require __DIR__ . '/app/config/routes.php';

$router->dispatch();
