<?php
require 'config.php';
require 'router.php';

// Controllers
require 'controllers/auth.php';
require 'controllers/employer.php';
require 'controllers/user.php';

// Initialize Router
$router = new Router();

// Auth
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');

// Employer
$router->post('/api/employer/job', 'EmployerController@add_job');
$router->put('/api/employer/job', 'EmployerController@edit_job');
$router->get('/api/employer/job', 'EmployerController@display_jobs');
$router->delete('/api/employer/job', 'EmployerController@delete_job');
$router->get('/api/employer/application', 'EmployerController@display_application');
$router->put('/api/employer/status', 'EmployerController@update_application_status');

// User
$router->post('/api/user/application', 'UserController@add_application');
$router->get('/api/user/application', 'UserController@display_application');
$router->post('/api/user/account', 'UserController@update_account');
$router->get('/api/user/job', 'UserController@display_jobs');

// Dispatch the request
$router->dispatch();
?>