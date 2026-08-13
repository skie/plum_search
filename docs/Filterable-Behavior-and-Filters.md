## Filter Registry

FilterRegistry implements filter’s collection management. FilterRegistry class extends CakePHP ObjectRegistry class.

## FilterableBehavior

Behavior provides search filters collection management and building final search query based on search data.
Collection management done based on the FilterRegistry class that extends CakePHP ObjectRegistry class.

### Provided methods

* ```filters``` - returns FilterRegistry instance.

* ```addFilter($name, array $options = [])``` - Adds a filter to table's FilterRegistry.

```php
    $this->addFilter('parameter', ['className' => 'Like']);
```

Required configuration argument is `className`. It is defined without `Filter` suffix, and searches in two folders: in PlumSearch.Model/Filter, and in src/Model/Filter for application itself.
Other parameters are filter specific.

* ```removeFilter($name)``` - Removes a filter from table's FilterRegistry.

```php
     $this->removeFilter('parameter');
```

### Provided finders

Implemented named search find('filters', $data) where $data contains search parameters' values.

### Behavior loading and filters initialization

Best place to load behavior and add filters is a Table::initialize method.

Example:

```php
    class UsersTable extends Cake\ORM\Table {
        public function initialize(array $config) {
            $this->addBehavior('PlumSearch.Filterable');
            $this->addFilter('username', ['className' => 'Like']);
            $this->addFilter('language', ['className' => 'Value']);
        }
    }
```

## Filters

Each filter implements logic on how search data is transformed into database query.
Plugin provides some default filters and allows you to implement user defined filters.

Each filter has the following configuration parameters.

* **name** — the required parameter that defines the filter name, and used as key for search data.
* **field** -the optional parameter that defines the query tables field for searching. Default equal to filter's **name** parameter.

###  Naming conventions

Filter classes should be located in the Model/Filter folder and have Filter as a suffix of class name.

### Value Filter

Value filter is the most simple filter that provides exact comparison of input data.

### Like Filter

The Like filter use LIKE operation comparison for input data.

### Range Filter

The Range filter uses a pair of values to build range operation comparison for input data.

### Multiple Filter

The Multiple filter allows searching against multiple fields for the same input.

### Custom Filter

The Like filter provides an easy way to inject custom query filters.

### User's filter creation

It is possible to implement filters that incorporate application-wide logic.
Additionally it is possible to define additional configuration parameters passed to the such filter.

One can think that such filters are the same as Custom Filters described in the previous paragraph, but there is an important difference. Custom filters allow to easily define table's level finder, and User's generic filter allow to define filters application wide, and also define own parameters that passed to filters during initialization.

For example if we have ```tags``` an ```taggable``` tables in the application and have multiple models that could be tagged then we can implement custom class TagsFilters with defined **\_apply** method that implements custom logic for how a query should be generated.


