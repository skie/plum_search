<?php
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
use Cake\ORM\Table;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class FilterComponent
 */
class FilterComponent extends Component
{
    /**
     * Parameters Registry
     *
     * @var \PlumSearch\FormParameter\ParameterRegistry
     */
    protected $_searchParameters = null;

    /**
     * Controller instance
     *
     * @var \Cake\Controller\Controller
     */
    protected $_controller;

    /**
     * Default config
     *
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'formName' => null,
        'action' => null,
        'filterEmptyParams' => true,
        'prohibitedParams' => ['page'],
    ];

    /**
     * Constructor
     *
     * @param ComponentRegistry $registry A ComponentRegistry this component can use to lazy load its components.
     * @param array $config Array of configuration settings.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->_controller = $registry->getController();
        $this->parameters(true);
    }

    /**
     * Returns parameters registry instance
     *
     * @param bool $reset Reset flag.
     * @return \PlumSearch\FormParameter\ParameterRegistry
     */
    public function parameters($reset = false)
    {
        if ($reset || is_null($this->_searchParameters)) {
            $this->_searchParameters = new ParameterRegistry($this->_controller, []);
            $parameters = (array)$this->config('parameters');
            foreach ($parameters as $parameter) {
                if (!empty($parameter['name'])) {
                    $this->addParam($parameter['name'], $parameter);
                }
            }
        }

        return $this->_searchParameters;
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
    public function addParam($name, array $options = [])
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
    public function removeParam($name)
    {
        $this->parameters()->unload($name);

        return $this;
    }

    /**
     * Implements Post Redirect Get flow method.
     * For POST requests builds redirection url and perform redirect to get action.
     * For GET requests add filters finder to passed into the method query and returns it.
     *
     * @param Table $table Table instance.
     * @param array $options Search parameters.
     * @return mixed
     */
    public function prg($table, $options = [])
    {
        $this->config($options);

        $formName = $this->_initParam('formName', $this->config('formName'));
        $action = $this->_initParam('action', $this->controller()->request->params['action']);

        $this->parameters()->config([
            'formName' => $formName,
        ]);
        if ($this->_controller->request->is(['post', 'put'])) {
            $this->_redirect($action);
        } elseif ($this->_controller->request->is('get')) {
            $this->_setViewData($formName);

            return $table->find('filters', $this->values());
        }

        return $table;
    }

    /**
     * Returns search values list
     *
     * @return array
     */
    public function values()
    {
        return $this->parameters()->values();
    }

    /**
     * Returns controller instance
     *
     * @return \Cake\Controller\Controller
     */
    public function controller()
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
    protected function _initParam($name, $default = null)
    {
        $param = $this->config($name);
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
    protected function _redirect($action)
    {
        $params = $this->controller()->request->params['pass'];
        $searchParams = array_diff_key(
            array_merge(
                $this->controller()->request->query,
                $this->values()
            ),
            array_flip(
                (array)$this->config('prohibitedParams')
            )
        );

        if ($this->config('filterEmptyParams')) {
            $searchParams = array_filter(
                    $searchParams,
                    function ($v, $k) {
                        if (($v === 0) || ($v === '0')) {
                            return true;
                        }
                        return (bool)$v;
                    },
                    ARRAY_FILTER_USE_BOTH
            );
        }
        $params['?'] = $searchParams;
        $params['action'] = $action;
        $this->controller()->redirect($params);
    }

    /**
     * Set up view data
     *
     * @param string $formName Form name.
     * @return void
     */
    protected function _setViewData($formName)
    {
        $this->controller()->request->data($formName, $this->parameters()->viewValues());
        $this->controller()->set('searchParameters', $this->parameters());
    }
}
