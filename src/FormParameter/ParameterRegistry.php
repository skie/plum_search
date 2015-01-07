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
namespace PlumSearch\FormParameter;

use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use PlumSearch\FormParameter\Exception\MissingParameterException;

/**
 * ParameterRegistry is a registry for loaded parameters
 *
 * Handles loading, constructing  for param class objects.
 */
class ParameterRegistry extends ObjectRegistry
{
    /**
     * The table that this collection was initialized with.
     *
     * @var \Cake\Controller\Controller
     */
    protected $_Controller = null;

    /**
     * Form name
     *
     * @var string
     */
    public $formName;

    /**
     * Constructor.
     *
     * @param \Cake\Controller\Controller $Controller Controller instance.
     * @param array $options Settings.
     */
    public function __construct(Controller $Controller = null, array $options = [])
    {
        if ($Controller) {
            $this->_Controller = $Controller;
            $modelClass = $this->_Controller->modelClass;
            $this->_formName = $this->_Controller->{$modelClass}->alias();
            $this->config($options);
        }
    }

    /**
     * Resolve a param class name.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param  string       $class Partial class name to resolve.
     * @return string|false Either the correct class name or false.
     */
    protected function _resolveClassName($class)
    {
        $result = App::className($class, 'FormParameter', 'Parameter');
        if ($result || strpos($class, '.') !== false) {
            return $result;
        }

        return App::className('PlumSearch.' . $class, 'FormParameter', 'Parameter');
    }

    /**
     * Throws an exception when a param is missing.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string $class  The class name that is missing.
     * @param string $plugin The plugin the param is missing in.
     * @return void
     * @throws \PlumSearch\FormParameter\Exception\MissingParameterException
     */
    protected function _throwMissingClassError($class, $plugin)
    {
        throw new MissingParameterException([
            'class' => $class . 'Parameter',
            'plugin' => $plugin,
        ]);
    }

    /**
     * Create the param instance.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string $class The class name to create.
     * @param string $alias The alias of the param.
     * @param array $config An array of config to use for the param.
     * @return \PlumSearch\FormParameter\BaseParameter The constructed param class.
     */
    protected function _create($class, $alias, $config)
    {
        if (empty($config['name'])) {
            $config['name'] = $alias;
        }
        $instance = new $class($this, $config);

        return $instance;
    }

    /**
     * Return collection of loaded parameters
     *
     * @return \Cake\Collection\Collection
     */
    public function collection()
    {
        return collection($this->_loaded);
    }

    /**
     * Returns parameter value based by it's name
     *
     * @param string $name Parameter name.
     * @return mixed
     */
    public function data($name = null)
    {
        if ($this->_Controller->request->is('get')) {
            if (empty($name)) {
                return $this->_Controller->request->query;
            } else {
                return $this->_Controller->request->query($name);
            }
        } elseif ($this->_Controller->request->is(['post', 'put'])) {
            if (empty($name)) {
                return $this->_Controller->request->data[$this->formName];
            } else {
                return $this->_Controller->request->data($this->fieldName($name));
            }
        }
        return null;
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function values()
    {
        $result = [];
        foreach ($this->collection() as $param) {
            $result = $result + $param->values();
        }

        return $result;
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function viewValues()
    {
        $result = [];
        foreach ($this->collection() as $param) {
            $result = $result + $param->viewValues();
        }

        return $result;
    }

    /**
     * Setup additional settings
     *
     * @param array $options Settings.
     * @return void
     */
    public function config($options)
    {
        if (!empty($options['formName'])) {
            $this->formName = $options['formName'];
        }
    }

    /**
     * Generates full form field name.
     *
     * @param string $name Field name.
     * @return string
     */
    public function fieldName($name)
    {
        if (empty($this->formName)) {
            return $name;
        }

        return $this->formName . '.' . $name;
    }
}
