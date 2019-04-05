## Filter Registry

FilterRegistry implements filters collection management. FilterRegistry class extends CakePHP ObjectRegistry class.

## FilterableBehavior

Behavior provides search filters collection management and building final search query based on search data.
Collection management done based on FilterRegistry class that extends CakePHP ObjectRegistry class.

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

Each filter implements logic how search data transformed into database query.
Plugin provides some default filters and allow to implements user defined filters.

Each filter have next configuration parameters.

* **name** - required parameter that define filter name, and used as key for search data.
* **field** - optional parameter that define query tables field for searching. Default equal to filter's **name** parameter.

###  Naming conventions

Filter classes should located in Model/Filter folder and has Filter as a suffix of class name.

### Value Filter

Value filter is most simple filter that provide exact comparison of input data.

### Like Filter

Like filter use LIKE operation comparison for input data.

### Multiple Filter

Multiple filter allow to search against multiple fields for same input.

### Custom Filter

Like filter provides easy way to inject custom query filters.

### User's filter creation

It is possible to implements filters that incorporate application-wide logic.
Additionally it is possible to define additional configuration parameters passed to such filter.

One can think that such filters is same as Custom Filters described in previous paragraph, but there is important difference. Custom filters allow to easily define table's level finder, and User's generic filter allow o define filters application wide, and also define own parameters that passed to filters during initialization.

For example if we have ```tags``` an ```taggable``` tables in the application and have multiple models that could be tagged then we can implement custom class TagsFilters with defined **\_apply** method that implements custom logic how query should generated.
