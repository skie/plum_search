<?php
namespace PlumSearch\Test\App\Config;

use Cake\Routing\Router;

Router::reload();
Router::scope('/', function ($routes) {
    $routes->fallbacks();
});
