<?php

declare(strict_types=1);

namespace PlumSearch\Test\App\Config;

use Cake\Routing\RouteBuilder;

$routes->connect(
    '/articles/autocomplete',
    ['controller' => 'Articles', 'action' => 'autocomplete']
);

$routes->scope('/', function (RouteBuilder $routes) {
    $routes->fallbacks();
});
