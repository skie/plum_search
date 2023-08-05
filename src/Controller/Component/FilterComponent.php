<?php
declare(strict_types=1);

/**
 * PlumSearch plugin for CakePHP Rapid Development Framework
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Evgeny Tomenko
 * @since         PlumSearch 0.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace PlumSearch\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ResponseEmitter;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class FilterComponent
 */
class FilterComponent extends Component
{
    /**
     * Parameters Registry
     */
    protected ?\PlumSearch\FormParameter\ParameterRegistry $_searchParameters = null;

    /**
     * Controller instance
     */
    protected \Cake\Controller\Controller $_controller;

    /**
     * Default config
     *
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'formName' => null,
        'action' => null,
        'filterEmptyParams' => true,
        'prohibitedParams' => ['page'],
    ];

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry A ComponentRegistry.
     * @param array $config Array of configuration settings.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->_controller = $registry->getController();
        $this->resetParameters();
    }

    /**
     * Returns parameters registry instance.
     *
     * @return \PlumSearch\FormParameter\ParameterRegistry|null
     */
    public function parameters(): ?ParameterRegistry
    {
        return $this->_searchParameters;
    }

    /**
     * Reset parameters registry instance.
     *
     * @return void
     */
    public function resetParameters(): void
    {
        $this->_searchParameters = new ParameterRegistry($this->_controller, []);
        $parameters = (array)$this->getConfig('parameters');
        foreach ($parameters as $parameter) {
            if (!empty($parameter['name'])) {
                $this->addParam($parameter['name'], $parameter);
            }
        }
    }

    /**
     * Add a parameter.
     *
     * Example:
     *
     * Load a filter, with some settings.
     *
     * {{{
     * $this->addParam('name', ['className' => 'Input']);
     * }}}
     *
     * @param  string $name The name of the parameter.
     * @param  array $options The options for the parameter to use.
     * @return \Cake\Controller\Component
     */
    public function addParam(string $name, array $options = []): Component
    {
        $this->parameters()->load($name, $options);

        return $this;
    }

    /**
     * Removes a param.
     *
     * Example:
     *
     * Remove a param.
     *
     * {{{
     * $this->removeFilter('name');
     * }}}
     *
     * @param  string                     $name The alias that the parameter was added with.
     * @return \Cake\Controller\Component
     */
    public function removeParam(string $name): Component
    {
        $this->parameters()->unload($name);

        return $this;
    }

    /**
     * Implements Post Redirect Get flow method.
     * For POST requests builds redirection url and perform redirect to get action.
     * For GET requests add filters finder to passed into the method query and returns it.
     *
     * @param \Cake\ORM\Table|\Cake\ORM\Query\SelectQuery $table Table instance.
     * @param array $options Search parameters.
     * @return mixed
     */
    public function prg($table, array $options = [])
    {
        $this->setConfig($options);

        $formName = (string)$this->_initParam('formName', $this->getConfig('formName'));
        $action = $this->_initParam('action', $this->controller()->getRequest()->getParam('action'));

        $this->parameters()->config([
            'formName' => $formName,
        ]);
        if ($this->_controller->getRequest()->is(['post', 'put'])) {
            $this->_redirect($action);
        } elseif ($this->_controller->getRequest()->is('get')) {
            $this->_setViewData($formName);
            return $table->find('filters', options: $this->values());
        }

        return $table;
    }

    /**
     * Returns search values list
     *
     * @return array
     */
    public function values(): array
    {
        return $this->parameters()->values();
    }

    /**
     * Returns controller instance
     *
     * @return \Cake\Controller\Controller
     */
    public function controller(): Controller
    {
        return $this->_controller;
    }

    /**
     * Initialize parameter
     *
     * @param string $name Parameter name.
     * @param mixed $default Default value.
     * @return mixed|string
     */
    protected function _initParam(string $name, $default = null)
    {
        $param = $this->getConfig($name);
        if (!$param) {
            return $default;
        }

        return $param;
    }

    /**
     * Redirect action method
     *
     * @param string $action Action name.
     * @return void
     */
    protected function _redirect(string $action): void
    {
        $params = $this->controller()->getRequest()->getParam('pass');
        $searchParams = array_diff_key(
            array_merge(
                (array)$this->controller()->getRequest()->getQuery(),
                $this->values()
            ),
            array_flip(
                (array)$this->getConfig('prohibitedParams')
            )
        );

        if ($this->getConfig('filterEmptyParams')) {
            $searchParams = array_filter(
                $searchParams,
                function ($v, $k): bool {
                    if (($v === 0) || ($v === '0')) {
                        return true;
                    }

                    return (bool)$v;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }
        $params['?'] = $searchParams;
        $params = array_merge($params, $searchParams);

        $params['action'] = $action;
        $this->controller()->redirect($params);

        $emitter = new ResponseEmitter();
        $emitter->emit($this->controller()->getResponse());
    }

    /**
     * Set up view data
     *
     * @param string $formName Form name.
     * @return void
     */
    protected function _setViewData(string $formName): void
    {
        $this->controller()->setRequest(
            $this->controller()->getRequest()->withData($formName, $this->parameters()->viewValues())
        );
        $this->controller()->set('searchParameters', $this->parameters());
    }
}
