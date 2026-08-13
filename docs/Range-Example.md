As a result of next steps we get form with two fields.

To implement search form you need perform next steps:

#### 1. In Table::initialize Range filter could be added like this.
```php
	$this->addFilter('created', ['className' => 'Range']);
```

#### 2. Load component and define range search parameter in controller initialize method.

```php
    public function initialize(): void {
        $this->loadComponent('PlumSearch.Filter', [
            'parameters' => [
				[
					'name' => 'created',
					'className' => 'Range',
					'postRenderCallback' => function($parameter, $view) {
						$field = function ($name, $field) {
							return "var $name = datepicker('#$field', { id: 1,
								 formatter: (input, date, instance) => {
									 var value = moment(date).format('YYYY-MM-DD');
									 input.value = value;
								   }
								   });";
						};
						$script = $field('start', 'created') . $field('end', 'created-to');

						return $view->Html->scriptBlock($script);
					}
				],
            ]
        ]);
	}
```

#### 3. For this example in layout was loaded next javascript libraries. Of course if you use any other library, it should support interaction with rendered html markup.

```php
    <?= $this->Html->script([
		'moment' // from https://momentjs.com/downloads/moment.js
	]) ?>
    <link rel="stylesheet" href="https://unpkg.com/js-datepicker/dist/datepicker.min.css">
    <script src="https://unpkg.com/js-datepicker"></script>

```
