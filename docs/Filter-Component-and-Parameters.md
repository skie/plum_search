## Filter Component

Component provides search parameters management, post-redirect-get pattern implementation and building search query based on form search parameters.

### Component initialization options

The component configuration contains next settings:

* **formName:** Defines form name, if not null search form supposed to have name defined. (Default: ```null```)
* **action:** Action for redirect, if not defined, uses the same action as post. (Default: ```null```)
* **prohibitedParams:** List of parameters that should be cleaned up before redirection. (Default: ```['page']```)

### Component methods

* ```addParam($name, array $options = [])``` - Adds a parameter to ParameterRegistry.

```php
    $this->Filter->addParam('field', ['className' => 'Input']);
```

Required configuration argument is `className`. It is defined without `Parameter` suffix, and searches in two folders: in PlumSearch.FormParameter, and in src/FormParameter for application itself.
Other options depend from Param class.

* ```removeParam($name)``` - Removes a parameter from ParameterRegistry.

```php
    $this->Filter->removeParam('field']);
```

* ```prg($table, $options = [])``` - Implements Post Redirect Get flow method. For POST requests build a redirection url and perform redirection to get action. For GET requests add filters finder to pass into the method query and return it.

```php
    public function index()
    {
        $this->set('articles', $this->Paginator->paginate($this->Filter->prg($this->Articles)));
    }
```

### Usage examples

## Parameters Registry

ParameterRegistry implements parameters collection management. ParameterRegistry class extends CakePHP ObjectRegistry class.

## Form Parameters

Form parameters represents search input fields and embed logic for getting additional info for displaying such parameters like lists, contains metadata for rendering parameters and implements logic to get current parameters values.

### Form parameter initialization options

Each  parameter have next settings:

* **name:** Unique parameter name.
* **field:** String defines a unique parameter name in form or query parameter.
* **visible:** Defines parameter visibility in form. If its value is false, the parameter is hidden.
* **formConfig:** Contains Form::input ```$options``` settings like class, input type, label name...

### Input parameter

InputParam is a text input form parameter.

### Select and Multiple parameters

SelectParam is a select box input form parameter.
MultipleParam used for multiple select box or multiple checkbox.

#### Additional constructor options:

Option **finder** is a query to ORM to get a list of options for select input generation.
Option **options** is a key-value array in list format for select input generation.

One of **finder** or **options** parameters are required

### Range parameter

RangeParam is a generic input having two inputs. It should be used in pairs with RangeFilter.

#### Additional constructor options:

**postRenderCallback** is callable, accepting BaseParameter instance as first argument, and View class instance as second. This method could be used to decorate parameters with additional javascript logic or for any different post processing purposes.

### Autocomplete parameter

Autocomplete parameter serves both server and client side autocomplete field management.

Autocomplete parameter internally create additional hidden parameter with have exact equal to parameter name and autocomplete input widget field is postfixed by **_lookup**.

Client side code has only **jQuery** library dependency.

#### Additional constructor options:

Option **autocompleteAction** is a callable function that accepts single arguments that contains a user search string for an autocomplete field, and returns an array where each row contains at least two keys: ```id``` and ```value```.

#### Autocomplete trait

Trait could be included into controllers that use autocomplete parameters. It provides an ```autocomplete``` action method.

Autocomplete action returns json object. This action accepts two query string parameters: ```paramName``` and ```query```. First used to get correct AutocompleteParameter instance and second used to pass to the method defined by **autocompleteAction** setting.

#### Autocomplete example

In controller load trait and define autocomplete parameter:

```php
class UsersController extends AppController {

    use AutocompleteTrait;

    public function initialize() {
        $role = $this->Users->Roles;
        $this->loadComponent('PlumSearch.Filter', [
            'parameters' => [
                [
                    'name' => 'role_id',
                    'className' => 'Autocomplete',
                    'autocompleteAction' => function($query) use ($role) {
                        return $role
                            ->find('all')
                            ->where(['name like' => '%' . $query . '%'])
                            ->formatResults(function($roles) {
                                return $roles->map(function ($role) {
                                    return [
                                        'id' => $role['id'],
                                        'value' => $role['name']
                                    ];
                                });
                            });
                    }
                ]
            ]
        ]);
    }
}
```

In view load js ```PlumSearch.autocomplete``` and css ```PlumSearch.autocomplete``` files:

```php
echo $this->Html->script('jquery-2.1.1.js');
echo $this->Html->css('PlumSearch.autocomplete');
echo $this->Html->script('PlumSearch.jquery.autocomplete');
echo $this->Html->script('PlumSearch.autocomplete');

echo $this->element('PlumSearch.search');
```
