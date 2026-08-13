# CakePHP PlumSearch Plugin

- [Introduction](#introduction)
- [Installation](#installation)
- [Quickstart](#quickstart)
- [Filterable Behavior](#filterable-behavior)
    - [Adding Filters](#adding-filters)
    - [Built-in Filters](#built-in-filters)
    - [Custom Filters](#custom-filters)
- [Filter Component](#filter-component)
    - [Component Configuration](#component-configuration)
    - [Form Parameters](#form-parameters)
    - [Post-Redirect-Get Pattern](#post-redirect-get-pattern)
- [Form Parameters](#form-parameters)
    - [Input Parameter](#input-parameter)
    - [Select Parameter](#select-parameter)
    - [Multiple Parameter](#multiple-parameter)
    - [Range Parameter](#range-parameter)
    - [Hidden Parameter](#hidden-parameter)
    - [Autocomplete Parameter](#autocomplete-parameter)
    - [Lookup Parameter](#lookup-parameter)
- [Search Helper](#search-helper)
    - [Rendering Search Forms](#rendering-search-forms)
    - [Custom Form Rendering](#custom-form-rendering)
- [Advanced Usage](#advanced-usage)
    - [Dependent Fields](#dependent-fields)
    - [Custom Filter Logic](#custom-filter-logic)

<a name="introduction"></a>
## Introduction

PlumSearch provides a comprehensive search and filtering system for CakePHP applications. The plugin allows you to easily implement advanced search forms with various input types, autocomplete functionality, range filtering, and custom filter logic.

The plugin follows CakePHP conventions and integrates seamlessly with the framework's ORM, making it simple to add powerful search capabilities to any table or controller. PlumSearch separates concerns by using behaviors for query filtering and components for form parameter management, allowing you to build flexible and maintainable search interfaces.

<a name="installation"></a>
## Installation

You may install PlumSearch using the Composer package manager:

```bash
composer require skie/cakephp-search
```

Once installed, you should load the plugin in your `src/Application.php` file:

```php
public function bootstrap(): void
{
    parent::bootstrap();

    $this->addPlugin('PlumSearch');
}
```

Alternatively, you can load the plugin using the CakePHP console:

```bash
bin/cake plugin load PlumSearch
```

The plugin does not require any additional configuration files or database migrations. All configuration is done through component and behavior settings in your application code.

<a name="quickstart"></a>
## Quickstart

To get started with PlumSearch, you need to perform three main steps: add the Filterable behavior to your table, configure the Filter component in your controller, and render the search form in your view.

First, add the Filterable behavior to your table and define which filters should be available. Filters determine how search parameters are applied to database queries:

```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior('PlumSearch.Filterable');
        $this->addFilter('username', ['className' => 'Like']);
        $this->addFilter('role_id', ['className' => 'Value']);
    }
}
```

Next, configure the Filter component in your controller. The component manages form parameters and implements the Post-Redirect-Get pattern to ensure clean URLs:

```php
<?php
namespace App\Controller;

use App\Controller\AppController;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('PlumSearch.Filter', [
            'parameters' => [
                ['name' => 'username', 'className' => 'Input'],
                [
                    'name' => 'role_id',
                    'className' => 'Select',
                    'finder' => $this->Users->Roles->find('list'),
                ],
            ],
        ]);
    }

    public function index()
    {
        $this->set('users', $this->Paginator->paginate(
            $this->Filter->prg($this->Users)
        ));
    }
}
```

Finally, render the search form in your view template. The plugin provides a convenient element that automatically renders all configured search parameters:

```php
<?= $this->element('PlumSearch.search'); ?>
```

That's it. Your search form is now functional. When users submit the form, the component handles the Post-Redirect-Get flow, and the behavior automatically applies the filters to your query.

<a name="filterable-behavior"></a>
## Filterable Behavior

The Filterable behavior extends your CakePHP Table classes with filtering capabilities. This behavior manages a collection of filters and applies them to queries based on search parameters. Filters define how search values are transformed into database query conditions.

The behavior provides a `find('filters', $data)` finder method that applies all registered filters to a query. The `$data` parameter contains the search parameter values, typically extracted from the request query string or form data.

<a name="adding-filters"></a>
### Adding Filters

Filters are added to your table in the `initialize` method using the `addFilter` method. Each filter requires a name and a configuration array. The `className` option specifies which filter class to use:

```php
public function initialize(array $config): void
{
    parent::initialize($config);

    $this->addBehavior('PlumSearch.Filterable');
    $this->addFilter('username', ['className' => 'Like']);
    $this->addFilter('status', ['className' => 'Value']);
}
```

Filter class names are specified without the `Filter` suffix. The plugin searches for filter classes in two locations: first in `PlumSearch\Model\Filter`, then in your application's `src/Model/Filter` directory. This allows you to create custom filters specific to your application.

Each filter configuration can include a `field` option that specifies which database field to filter on. If not specified, the filter name is used as the field name. You can also use dot notation to reference fields from associated tables:

```php
$this->addFilter('author_name', [
    'className' => 'Like',
    'field' => 'Authors.name',
]);
```

Filters can be removed dynamically using the `removeFilter` method:

```php
$this->removeFilter('username');
```

> [!NOTE]
> **CakePHP 5.3 Compatibility**: In CakePHP 5.3, calling behavior methods directly on table instances is deprecated. To avoid deprecation warnings, use the `FilterableTrait` in your table classes. The trait acts as a shim that forwards method calls to the behavior, ensuring compatibility with CakePHP 5.3 while maintaining the same API.

For CakePHP 5.3 compatibility, add the `FilterableTrait` to your table class:

```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use PlumSearch\Model\Behavior\FilterableTrait;

class UsersTable extends Table
{
    use FilterableTrait;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior('PlumSearch.Filterable');
        $this->addFilter('username', ['className' => 'Like']);
    }
}
```

The trait provides the same `addFilter`, `removeFilter`, and `filters` methods but calls them through the behavior properly, eliminating deprecation warnings.

<a name="built-in-filters"></a>
### Built-in Filters

PlumSearch includes several built-in filters that cover common search scenarios. The Value filter performs exact matching and is useful for filtering by specific values like status codes or foreign keys:

```php
$this->addFilter('status', ['className' => 'Value']);
```

When the search value is an array, the Value filter automatically uses an `IN` clause to match any of the provided values. This makes it work seamlessly with multiple select parameters.

The Like filter performs pattern matching using SQL `LIKE` operations. It automatically wraps the search value with wildcards, so searching for "john" will match "john", "johnson", and "johnny":

```php
$this->addFilter('username', ['className' => 'Like']);
```

The Range filter works with pairs of values to create range queries. It expects two parameters: the filter name for the minimum value and the filter name with a `_to` suffix for the maximum value:

```php
$this->addFilter('created', ['className' => 'Range']);
```

This filter will look for `created` and `created_to` in the search data. You can customize the suffix using the `depSuffix` configuration option.

The Multiple filter allows you to search across multiple fields with a single input. This is useful for implementing general search functionality where users can search across several fields at once:

```php
$this->addFilter('search', [
    'className' => 'Multiple',
    'fields' => ['title', 'description', 'content'],
    'type' => 'or',
]);
```

The `fields` option specifies which fields to search, and the `type` option determines whether to use `AND` or `OR` logic. When `type` is set to `or`, the filter matches records where any of the specified fields contain the search term. When set to `and` or empty, all fields must match.

<a name="custom-filters"></a>
### Custom Filters

For more complex filtering logic, you can use the Custom filter. This filter accepts a `method` configuration option that should be a callable function. The function receives the query, field name, value, search data, and filter configuration:

```php
$this->addFilter('price_range', [
    'className' => 'Custom',
    'method' => function ($query, $field, $value, $data, $config) {
        $min = $data['price_min'] ?? null;
        $max = $data['price_max'] ?? null;

        if ($min !== null) {
            $query = $query->where([$field . ' >=' => $min]);
        }
        if ($max !== null) {
            $query = $query->where([$field . ' <=' => $max]);
        }

        return $query;
    },
]);
```

You can also create reusable filter classes by extending `AbstractFilter`. Place your custom filter classes in `src/Model/Filter` with a `Filter` suffix. For example, create `src/Model/Filter/TagsFilter.php`:

```php
<?php
namespace App\Model\Filter;

use Cake\ORM\Query\SelectQuery;
use PlumSearch\Model\Filter\AbstractFilter;

class TagsFilter extends AbstractFilter
{
    protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery
    {
        return $query->matching('Tags', function ($q) use ($value) {
            return $q->where(['Tags.name IN' => (array)$value]);
        });
    }
}
```

Then use it in your table:

```php
$this->addFilter('tags', ['className' => 'Tags']);
```

<a name="filter-component"></a>
## Filter Component

The Filter component manages form parameters and implements the Post-Redirect-Get pattern for search forms. This pattern ensures that search parameters appear in the URL query string, making search results bookmarkable and shareable.

The component maintains a registry of form parameters, each representing a search input field. Parameters handle value extraction from requests, provide metadata for form rendering, and can include additional logic like autocomplete functionality.

<a name="component-configuration"></a>
### Component Configuration

The Filter component accepts several configuration options. The `formName` option defines the form name used when extracting search data from POST requests. If not specified, the component extracts data directly from the request:

```php
$this->loadComponent('PlumSearch.Filter', [
    'formName' => 'search',
    'parameters' => [
        // ...
    ],
]);
```

The `action` option specifies which controller action to redirect to after form submission. If not specified, the component redirects to the same action that received the POST request:

```php
$this->loadComponent('PlumSearch.Filter', [
    'action' => 'search',
    'parameters' => [
        // ...
    ],
]);
```

The `prohibitedParams` option lists parameter names that should be excluded from the redirect URL. By default, this includes `page` to prevent pagination parameters from interfering with search:

```php
$this->loadComponent('PlumSearch.Filter', [
    'prohibitedParams' => ['page', 'sort', 'direction'],
    'parameters' => [
        // ...
    ],
]);
```

The `filterEmptyParams` option controls whether empty parameters should be included in the redirect URL. When set to `true`, empty values are filtered out, keeping URLs clean. The default value is `true`:

```php
$this->loadComponent('PlumSearch.Filter', [
    'filterEmptyParams' => true,
    'parameters' => [
        // ...
    ],
]);
```

<a name="form-parameters"></a>
### Form Parameters

Form parameters are defined in the component's `parameters` configuration option. Each parameter requires a `name` and a `className`. The component searches for parameter classes in `PlumSearch\FormParameter` and your application's `src/FormParameter` directory:

```php
$this->loadComponent('PlumSearch.Filter', [
    'parameters' => [
        ['name' => 'username', 'className' => 'Input'],
        ['name' => 'role_id', 'className' => 'Select'],
    ],
]);
```

You can add parameters dynamically using the `addParam` method:

```php
$this->Filter->addParam('status', ['className' => 'Select']);
```

Parameters can be removed using the `removeParam` method:

```php
$this->Filter->removeParam('status');
```

The component provides a `prg` method that implements the Post-Redirect-Get pattern. For POST requests, it builds a redirect URL with search parameters and performs the redirect. For GET requests, it applies the filters finder to the provided table or query and returns the filtered query:

```php
public function index()
{
    $query = $this->Filter->prg($this->Users);
    $this->set('users', $this->Paginator->paginate($query));
}
```

The `prg` method also sets up view variables automatically. It sets `searchParameters` to the ParameterRegistry instance, which is used by the search form element to render the form fields.

<a name="post-redirect-get-pattern"></a>
### Post-Redirect-Get Pattern

The Post-Redirect-Get pattern prevents duplicate form submissions and ensures search parameters appear in the URL. When a user submits a search form via POST, the component extracts the search parameters, builds a redirect URL with those parameters as query string values, and redirects to a GET action.

This approach has several benefits. Search URLs become bookmarkable and shareable because all parameters are in the query string. Browser refresh works correctly without resubmitting the form. And pagination links automatically include search parameters when using CakePHP's Paginator component.

The component handles this automatically when you call the `prg` method. You don't need to manually check request methods or build redirect URLs.

<a name="form-parameters"></a>
## Form Parameters

Form parameters represent search input fields in your forms. Each parameter type provides different functionality and rendering options. Parameters handle value extraction from requests, provide form input configuration, and can include additional features like autocomplete or dependent field logic.

All parameters share common configuration options. The `name` option is required and defines the parameter identifier. The `field` option specifies the form field name, defaulting to the parameter name if not specified. The `visible` option controls whether the parameter appears in the form, and the `formConfig` option allows you to customize the form input options:

```php
[
    'name' => 'username',
    'className' => 'Input',
    'field' => 'user_name',
    'visible' => true,
    'formConfig' => [
        'class' => 'form-control',
        'placeholder' => 'Enter username',
    ],
]
```

<a name="input-parameter"></a>
### Input Parameter

The Input parameter creates a standard text input field. It's the simplest parameter type and is useful for text-based searches:

```php
['name' => 'username', 'className' => 'Input']
```

You can customize the input using the `formConfig` option:

```php
[
    'name' => 'email',
    'className' => 'Input',
    'formConfig' => [
        'type' => 'email',
        'placeholder' => 'Enter email address',
    ],
]
```

<a name="select-parameter"></a>
### Select Parameter

The Select parameter creates a dropdown select box. It requires either an `options` array or a `finder` query to populate the select options:

```php
[
    'name' => 'role_id',
    'className' => 'Select',
    'finder' => $this->Users->Roles->find('list'),
]
```

Alternatively, you can provide a static options array:

```php
[
    'name' => 'status',
    'className' => 'Select',
    'options' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
    ],
]
```

The finder option accepts any CakePHP query object. The query results are automatically formatted for use in a select box. If you need more control over the options, you can use query formatting methods:

```php
[
    'name' => 'category_id',
    'className' => 'Select',
    'finder' => $this->Products->Categories
        ->find('list')
        ->where(['Categories.active' => true])
        ->order(['Categories.name' => 'ASC']),
]
```

<a name="multiple-parameter"></a>
### Multiple Parameter

The Multiple parameter creates a multi-select dropdown or checkbox group, allowing users to select multiple values. It extends the Select parameter and shares the same options configuration:

```php
[
    'name' => 'tags',
    'className' => 'Multiple',
    'finder' => $this->Posts->Tags->find('list'),
]
```

When used with the Value filter, the Multiple parameter automatically works with array values, creating an `IN` clause in the query.

<a name="range-parameter"></a>
### Range Parameter

The Range parameter creates two input fields for specifying a range of values. It's commonly used for date ranges or numeric ranges. The parameter creates a primary field and a dependent field with a `_to` suffix:

```php
[
    'name' => 'created',
    'className' => 'Range',
]
```

This creates two fields: `created` and `created_to`. You can customize the suffix using configuration, but the default `_to` suffix works well for most cases.

The Range parameter supports a `postRenderCallback` option that allows you to add custom JavaScript for date pickers or other range input enhancements:

```php
[
    'name' => 'created',
    'className' => 'Range',
    'postRenderCallback' => function ($parameter, $view) {
        return $view->Html->scriptBlock("
            $('#created').datepicker();
            $('#created-to').datepicker();
        ");
    },
]
```

<a name="hidden-parameter"></a>
### Hidden Parameter

The Hidden parameter creates a hidden input field. It's useful for including search parameters that shouldn't be visible to users but need to be included in the search:

```php
[
    'name' => 'user_id',
    'className' => 'Hidden',
]
```

Hidden parameters are automatically set to not visible, so they won't appear in the rendered form. They still participate in value extraction and filtering.

<a name="autocomplete-parameter"></a>
### Autocomplete Parameter

The Autocomplete parameter provides server-side autocomplete functionality. It creates a text input field with autocomplete capabilities and a hidden field to store the selected value's ID:

```php
[
    'name' => 'role_id',
    'className' => 'Autocomplete',
    'autocompleteAction' => function ($query) {
        return $this->Users->Roles
            ->find('all')
            ->where(['name LIKE' => '%' . $query . '%'])
            ->formatResults(function ($roles) {
                return $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'value' => $role->name,
                    ];
                });
            });
    },
]
```

The `autocompleteAction` option must be a callable that accepts a search query string and returns an array of results. Each result must contain at least `id` and `value` keys.

To enable autocomplete functionality, you need to include the AutocompleteTrait in your controller and add an autocomplete action route:

```php
<?php
namespace App\Controller;

use App\Controller\AppController;
use PlumSearch\Controller\AutocompleteTrait;

class UsersController extends AppController
{
    use AutocompleteTrait;

    // ...
}
```

The trait provides an `autocomplete` action that handles autocomplete requests. The action expects `paramName` and `query` query string parameters.

In your view, you need to include the autocomplete JavaScript and CSS:

```php
echo $this->Html->css('PlumSearch.autocomplete');
echo $this->Html->script('PlumSearch.jquery.autocomplete');
echo $this->Html->script('PlumSearch.autocomplete');
```

The autocomplete implementation requires jQuery. The JavaScript automatically handles user input, makes requests to the autocomplete endpoint, and updates the hidden field with the selected value.

<a name="lookup-parameter"></a>
### Lookup Parameter

The Lookup parameter provides advanced autocomplete functionality that works with existing endpoints. Unlike the Autocomplete parameter, it doesn't require a callback function and can work with any JSON endpoint that returns autocomplete data:

```php
[
    'name' => 'country_id',
    'className' => 'Lookup',
    'autocompleteUrl' => '/countries/autocomplete.json',
]
```

The `autocompleteUrl` option specifies the endpoint URL. If not provided, it defaults to `/admin/{paramName}s/autocomplete.json` based on the parameter name.

The Lookup parameter supports several configuration options for customizing the autocomplete behavior. The `idName` option specifies which property in the response contains the ID value, defaulting to `id`. The `valueName` option specifies which property contains the display value, defaulting to `name`:

```php
[
    'name' => 'user_id',
    'className' => 'Lookup',
    'autocompleteUrl' => '/users/search.json',
    'idName' => 'user_id',
    'valueName' => 'full_name',
]
```

The `query` option defines the query string format for autocomplete requests. The default is `search=%QUERY`, where `%QUERY` is replaced with the user's search term. You can customize this format:

```php
[
    'name' => 'product_id',
    'className' => 'Lookup',
    'autocompleteUrl' => '/products/autocomplete.json',
    'query' => 'q=%QUERY&category=electronics',
    'wildcard' => '%QUERY',
]
```

The `minLength` option specifies the minimum number of characters required before triggering autocomplete, defaulting to 2. The `delay` option specifies the delay in milliseconds between keystrokes before triggering autocomplete, defaulting to 300:

```php
[
    'name' => 'customer_id',
    'className' => 'Lookup',
    'autocompleteUrl' => '/customers/autocomplete.json',
    'minLength' => 3,
    'delay' => 500,
]
```

The Lookup parameter supports dependent fields through the `parentField` option. When a parent field is specified, the lookup field is disabled until the parent has a value:

```php
[
    'name' => 'city_id',
    'className' => 'Lookup',
    'autocompleteUrl' => '/cities/autocomplete.json',
    'parentField' => 'country_id',
]
```

When the parent field changes, dependent fields are automatically reset. You can specify multiple dependent fields using the `dependentFields` option:

```php
[
    'name' => 'country_id',
    'className' => 'Lookup',
    'autocompleteUrl' => '/countries/autocomplete.json',
    'dependentFields' => ['city_id', 'region_id'],
]
```

The Lookup parameter's client-side implementation uses modern JavaScript and has no external library dependencies beyond what's included with the plugin.

<a name="search-helper"></a>
## Search Helper

The Search helper provides methods for rendering search forms based on configured parameters. It converts parameter configurations into form input configurations that can be used with CakePHP's Form helper.

<a name="rendering-search-forms"></a>
### Rendering Search Forms

The easiest way to render a search form is using the provided `PlumSearch.search` element. This element automatically renders all configured search parameters:

```php
<?= $this->element('PlumSearch.search'); ?>
```

The element accepts several options. The `formOptions` option allows you to customize the form tag:

```php
<?= $this->element('PlumSearch.search', [
    'formOptions' => [
        'id' => 'user-search-form',
        'class' => 'search-form',
    ],
]); ?>
```

The `inputOptions` option allows you to override input configurations for specific parameters:

```php
<?= $this->element('PlumSearch.search', [
    'inputOptions' => [
        'username' => [
            'class' => 'custom-input-class',
            'placeholder' => 'Search by username',
        ],
    ],
]); ?>
```

The `searchParameters` variable is automatically set by the Filter component when you call the `prg` method. It contains the ParameterRegistry instance with all configured parameters.

<a name="custom-form-rendering"></a>
### Custom Form Rendering

If you need more control over form rendering, you can use the Search helper methods directly. The `controls` method returns an array of form input configurations:

```php
$searchInputs = $this->Search->controls($searchParameters);
echo $this->Form->create();
echo $this->Form->controls($searchInputs);
echo $this->Form->button('Search');
echo $this->Form->end();
```

The `control` method returns the configuration for a single parameter:

```php
$usernameInput = $this->Search->control(
    $searchParameters->get('username'),
    ['class' => 'custom-class']
);
echo $this->Form->control('username', $usernameInput);
```

The `postRender` method executes post-render callbacks for parameters that have them. This is useful for adding JavaScript or other client-side enhancements:

```php
echo $this->Search->postRender($searchParameters);
```

This method is automatically called by the search element, but you can call it manually if you're building a custom form.

<a name="advanced-usage"></a>
## Advanced Usage

PlumSearch provides several advanced features for complex search scenarios. Dependent fields allow you to create cascading selects where one field's options depend on another field's value. Custom filter logic enables you to implement complex query conditions that go beyond the built-in filters.

<a name="dependent-fields"></a>
### Dependent Fields

The Lookup parameter supports dependent fields through the `parentField` configuration. When a lookup field depends on a parent field, it's automatically disabled until the parent has a value, and it's reset when the parent value changes.

For more complex dependent field scenarios, you can use JavaScript in the `postRenderCallback` to implement custom logic. For example, you might want to populate a select field's options based on another field's value:

```php
[
    'name' => 'city_id',
    'className' => 'Select',
    'postRenderCallback' => function ($parameter, $view) {
        return $view->Html->scriptBlock("
            $('#country-id').on('change', function() {
                var countryId = $(this).val();
                $.ajax({
                    url: '/cities/list.json',
                    data: {country_id: countryId},
                    success: function(data) {
                        var select = $('#city-id');
                        select.empty();
                        $.each(data, function(key, value) {
                            select.append($('<option></option>')
                                .attr('value', key).text(value));
                        });
                    }
                });
            });
        ");
    },
]
```

<a name="custom-filter-logic"></a>
### Custom Filter Logic

For complex filtering scenarios, you can create custom filter classes that implement sophisticated query logic. Custom filters can access associated tables, perform subqueries, or implement complex conditional logic:

```php
<?php
namespace App\Model\Filter;

use Cake\ORM\Query\SelectQuery;
use PlumSearch\Model\Filter\AbstractFilter;

class RecentActivityFilter extends AbstractFilter
{
    protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery
    {
        if ($value === 'recent') {
            $days = $data['days'] ?? 7;
            $date = new \DateTime("-$days days");

            return $query->where([
                $field . ' >=' => $date->format('Y-m-d H:i:s'),
            ]);
        }

        return $query;
    }
}
```

Custom filters can also use the filter configuration to access additional options:

```php
$this->addFilter('activity', [
    'className' => RecentActivityFilter::class,
    'defaultDays' => 30,
]);
```
