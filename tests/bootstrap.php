<?php
declare(strict_types=1);

$findRoot = function () {
    $root = dirname(__DIR__);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(__DIR__, 2);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(__DIR__, 3);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }
};

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', $findRoot());
define('APP_DIR', 'App');
define('WEBROOT_DIR', 'webroot');
define('APP', ROOT . '/tests/App/');
define('CONFIG', ROOT . '/tests/Config/');
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TESTS', ROOT . DS . 'tests' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require ROOT . '/vendor/cakephp/cakephp/src/basics.php';
require ROOT . '/vendor/autoload.php';

Cake\Core\Configure::write('App', ['namespace' => 'PlumSearch\Test\App']);
// Cake\Core\Configure::write('Error', ['errorLevel' => E_ALL ^ E_USER_DEPRECATED]);
Cake\Core\Configure::write('debug', true);

@mkdir(TMP . 'cache/models', 0777);
@mkdir(TMP . 'cache/persistent', 0777);
@mkdir(TMP . 'cache/views', 0777);

$cache = [
    'default' => [
        'engine' => 'File',
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => 'search_myapp_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds',
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => 'search_my_app_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds',
    ],
];

Cake\Cache\Cache::setConfig($cache);
Cake\Core\Configure::write('Session', [
    'defaults' => 'php',
]);

Cake\Core\Configure::write('App.encoding', 'utf8');

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'timezone' => 'UTC',
]);

// Create test database schema
use Cake\TestSuite\Fixture\SchemaLoader;

if (env('FIXTURE_SCHEMA_METADATA')) {
    $loader = new SchemaLoader();
    $loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
