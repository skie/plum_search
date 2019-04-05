# Installation

## How to include
Installing the Plugin is pretty much as with every other CakePHP Plugin.

Put the files in `ROOT/plugins/PlumSearch`, using Packagist/Composer:
```
"require": {
    "skie/cakephp-search": "dev-master"
}
```
and

    composer update

Details @ https://packagist.org/packages/skie/plum-search

This will load the plugin (within your boostrap file):
```php
Plugin::load('PlumSearch');
```
or
```php
Plugin::loadAll(...);
```

### Internal handling via plugin dot notation
Internally (method access), you don't use the namespace declaration. The plugin name suffices:
```php
// In a Table
$this->addBehavior('PlumSearch.Filterable');

// In a Controller
public $helpers = ['PlumSearch.Search'];
```
