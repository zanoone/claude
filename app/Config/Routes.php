<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'auth']);

// Auth Routes
$routes->group('auth', static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::login');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::register');
    $routes->get('logout', 'AuthController::logout');
});

// User Routes
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('profile', 'Home::profile');
});

// Admin Routes
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('users', 'AdminController::users');
    $routes->get('users/edit/(:num)', 'AdminController::userEdit/$1');
    $routes->post('users/edit/(:num)', 'AdminController::userEdit/$1');
    $routes->get('users/delete/(:num)', 'AdminController::userDelete/$1');
    $routes->get('chat-rooms', 'AdminController::chatRooms');
    $routes->get('settings', 'AdminController::settings');
    $routes->post('settings', 'AdminController::settings');
});

// Chat Routes
$routes->group('chat', ['filter' => 'auth'], static function ($routes) {
    $routes->get('room/(:num)', 'ChatController::room/$1');
    $routes->post('send-message', 'ChatController::sendMessage');
    $routes->get('get-messages/(:num)', 'ChatController::getMessages/$1');
    $routes->get('create-room', 'ChatController::createRoom');
    $routes->post('create-room', 'ChatController::createRoom');
    $routes->get('delete-room/(:num)', 'ChatController::deleteRoom/$1');
});
