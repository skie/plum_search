<?php

declare(strict_types=1);

namespace PlumSearch\Test\App\Config;

use Cake\Routing\Router;

Router::reload();

Router::connect(
    '/articles/autocomplete',
    ['controller' => 'Articles', 'action' => 'autocomplete']
);

Router::scope('/', function ($routes) {
    $routes->fallbacks();
});
