As a result of next steps we get form with two fields: string search field by username, and select box with list of roles.
To implement search form you need perform next steps:

## 1. Load behavior and add filters in Table::initialize method

```php
class UsersTable extends Table {

    public function initialize(array $config) {
        $this->addBehavior('PlumSearch.Filterable');
        $this->addFilter('username', ['className' => 'Like']);
        $this->addFilter('role_id', ['className' => 'Value']);        
    }

}
``` 

## 2. Load component and define search parameters

```php
class UsersController extends AppController {

    public $helpers = [
        'PlumSearch.Search'
    ];

    public function initialize() {
        $this->loadComponent('PlumSearch.Filter', [
            'parameters' => [
                ['name' => 'username', 'className' => 'Input'],
                [
                    'name' => 'role_id',
                    'className' => 'Autocomplete',
                     'finder' => $this->Users->Roles->find('list'),
                ]
            ]
        ]);

}
``` 

## 3. Call Filter::prg method

```php
    public function index() {
        $this->set('users', $this->Paginator->paginate($this->Filter->prg($this->Users)));
    }
```

## 4. Render search form

```php
    <?= $this->element('PlumSearch.search'); ?>
```
