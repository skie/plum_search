# Installation

## How to include
Installing the Plugin is pretty much as with every other CakePHP Plugin.

```bash
    composer require skie/cakephp-search
```

Details @ https://packagist.org/packages/skie/plum-search

This will load the plugin (within your Applicaion class):
```php
$this->addPlugin('PlumSearch');
```

### Internal handling via plugin dot notation
Internally (method access), you don't use the namespace declaration. The plugin name suffices:
```php
// In a Table
$this->addBehavior('PlumSearch.Filterable');

// In a Controller
public $helpers = ['PlumSearch.Search'];
```

or inside AppView::initialize method you can load it globally
```php
    $this->loadHelper('PlumSearch.Search');
```
