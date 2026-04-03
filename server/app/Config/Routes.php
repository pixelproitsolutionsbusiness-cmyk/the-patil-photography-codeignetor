<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Routes for direct access in subdirectory (e.g. /api/popup)
$routes->get('settings', 'Settings::index');
$routes->put('settings', 'Settings::update');
$routes->post('settings', 'Settings::update');
$routes->post('auth/login', 'Auth::login');
$routes->post('auth/logout', 'Auth::logout');
$routes->get('dashboard/stats', 'Dashboard::stats');
$routes->resource('orders', ['controller' => 'Orders']);
$routes->resource('accessories', ['controller' => 'Accessories']);
$routes->resource('users', ['controller' => 'Users']);
$routes->post('users/reveal', 'Users::reveal');
$routes->resource('gallery', ['controller' => 'Gallery']);
$routes->resource('films', ['controller' => 'Films']);
$routes->resource('services', ['controller' => 'Services']);
$routes->resource('event-types', ['controller' => 'EventTypes']);
$routes->resource('clients', ['controller' => 'Clients']);
$routes->resource('enquiries', ['controller' => 'Enquiries']);
$routes->resource('contacts', ['controller' => 'Contacts']);
$routes->resource('invoices', ['controller' => 'Invoices']);
$routes->resource('quotations', ['controller' => 'Quotations']);
$routes->resource('payments', ['controller' => 'Payments']);
$routes->resource('slider', ['controller' => 'Slider']);
$routes->resource('testimonials', ['controller' => 'Testimonials']);
$routes->resource('love-stories', ['controller' => 'LoveStories']);
$routes->resource('team', ['controller' => 'Team']);
$routes->resource('popup', ['controller' => 'Popup']);
$routes->get('migrate-images', 'MigrateImages::index');

// Routes for root access with /api/ prefix (local dev)
$routes->group('api', function($routes) {
    $routes->get('settings', 'Settings::index');
    $routes->put('settings', 'Settings::update');
    $routes->post('settings', 'Settings::update');

    $routes->post('auth/login', 'Auth::login');
    $routes->post('auth/logout', 'Auth::logout');

    $routes->get('dashboard/stats', 'Dashboard::stats');

    $routes->resource('orders', ['controller' => 'Orders']);
    $routes->resource('accessories', ['controller' => 'Accessories']);
    $routes->resource('users', ['controller' => 'Users']);
    $routes->post('users/reveal', 'Users::reveal');

    $routes->resource('gallery', ['controller' => 'Gallery']);

    $routes->resource('films', ['controller' => 'Films']);
    $routes->resource('services', ['controller' => 'Services']);
    $routes->resource('event-types', ['controller' => 'EventTypes']);
    $routes->resource('clients', ['controller' => 'Clients']);
    $routes->resource('enquiries', ['controller' => 'Enquiries']);
    $routes->resource('contacts', ['controller' => 'Contacts']);
    $routes->resource('invoices', ['controller' => 'Invoices']);
    $routes->resource('quotations', ['controller' => 'Quotations']);
    $routes->resource('payments', ['controller' => 'Payments']);
    $routes->resource('slider', ['controller' => 'Slider']);
    $routes->resource('testimonials', ['controller' => 'Testimonials']);
    $routes->resource('love-stories', ['controller' => 'LoveStories']);
    $routes->resource('team', ['controller' => 'Team']);
    $routes->resource('popup', ['controller' => 'Popup']);
    $routes->get('migrate-images', 'MigrateImages::index');
});

$routes->get('db-test', 'DbTest::index');
$routes->get('debug-check', function() { return "API reachable"; });

