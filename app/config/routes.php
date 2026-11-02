<?php

require_once __DIR__ . '/../core/Router.php';

$router = new Router();

// Entry & Dashboard
$router->get('/', ['DashboardController', 'index']);
$router->get('/dashboard', ['DashboardController', 'index']);

// Authentication
$router->get('/login', ['AuthController', 'login']);
$router->post('/login', ['AuthController', 'login']);

$router->get('/register', ['AuthController', 'register']);
$router->post('/register', ['AuthController', 'register']);

$router->get('/logout', ['AuthController', 'logout']);

// Lead Management
$router->get('/leads', ['LeadController', 'index']);
$router->post('/leads/store', ['LeadController', 'store']);
$router->post('/leads/update', ['LeadController', 'update']);
$router->post('/leads/delete', ['LeadController', 'destroy']);
$router->get('/leads/get', ['LeadController', 'getLead']);
$router->get('/leads/export', ['LeadController', 'export']);

// Other Business Modules
$router->get('/followups',         ['FollowUpController', 'index']);
$router->post('/followups/store',  ['FollowUpController', 'store']);
$router->post('/followups/update', ['FollowUpController', 'update']);
$router->post('/followups/delete', ['FollowUpController', 'destroy']);
$router->post('/followups/complete',['FollowUpController','complete']);
$router->get('/followups/get',     ['FollowUpController', 'get']);
$router->get('/followups/export',  ['FollowUpController', 'export']);

$router->get('/users',             ['UserController',     'index']);
$router->post('/users/store',      ['UserController',     'store']);
$router->post('/users/update',     ['UserController',     'update']);
$router->post('/users/toggle-status', ['UserController', 'toggleStatus']);
$router->post('/users/delete',     ['UserController',     'destroy']);

$router->get('/reports',           ['ReportController',   'index']);
$router->get('/reports/export',    ['ReportController',   'export']);

$router->get('/settings',          ['SettingController',  'index']);
$router->post('/settings/profile', ['SettingController',  'updateProfile']);
$router->post('/settings/password',['SettingController',  'updatePassword']);

return $router;
