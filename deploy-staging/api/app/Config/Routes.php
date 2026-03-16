<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    $routes->get('settings', 'Settings::index');
    $routes->put('settings', 'Settings::update');
    $routes->post('settings', 'Settings::update'); // For those who use POST for update
    
    // Add more resource routes as controllers are created
    $routes->resource('gallery');
    $routes->resource('films');
    $routes->resource('services');
    $routes->resource('event-types');
    $routes->resource('clients');
    $routes->resource('enquiries');
    $routes->resource('contacts');
    $routes->resource('orders');
    $routes->resource('invoices');
    $routes->resource('quotations');
    $routes->resource('payments');
    $routes->resource('slider');
    $routes->resource('testimonials');
    $routes->resource('love-stories');
    $routes->resource('team');
    $routes->resource('popup');
});
