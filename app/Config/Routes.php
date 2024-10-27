<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Users Routes
$routes->post('users', 'UserController::create');
$routes->get('users', 'UserController::filterUsers');
$routes->get('users/(:num)', 'UserController::show/$1');

// Tasks Routes
$routes->post('tasks', 'TaskController::create');
$routes->get('tasks/(:num)', 'TaskController::show/$1');
$routes->get('tasks', 'TaskController::filterTasks');
$routes->get('tasks/user', 'TaskController::getTasksByUser');
$routes->get('tasks/user/(:num)', 'TaskController::getTasksByUser/$1');
$routes->put('tasks/(:num)', 'TaskController::update/$1');
// $routes->delete('tasks/(:num)', 'TaskController::delete/$1');
$routes->delete('tasks/(:num)', 'Api\TaskController::delete/$1');

// Comments Routes
$routes->post('tasks/(:num)/comments', 'CommentController::create');
$routes->get('tasks/(:num)/comments', 'CommentController::show/$1');
$routes->get('/tasks/comments', 'CommentController::show');

// Auth Routes
$routes->post('auth/login', 'AuthController::login');
// $routes->post('auth/logout', 'AuthController::logout');
$routes->post('auth/logout', 'AuthController::logout');
