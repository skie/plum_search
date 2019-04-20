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
    protected $formName;

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
            $this->config($options);
        }
    }

    /**
     * Form name getter.
     *
     * @return string
     */
    public function getFormName(): string
    {
        return (string)$this->formName;
    }

    /**
     * Resolve a param class name.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param  string       $class Partial class name to resolve.
     * @return string|null Either the correct class name or false.
     */
    protected function _resolveClassName($class): ?string
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
    protected function _throwMissingClassError(string $class, ?string $plugin): void
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
    protected function _create($class, string $alias, array $config): \PlumSearch\FormParameter\BaseParameter
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
     * @param callable $collectionMethod Collection method to execute.
     * @return \Cake\Collection\Collection
     */
    public function collection(callable $collectionMethod = null): \Cake\Collection\Collection
    {
        $collection = collection($this->_loaded);
        if (is_callable($collectionMethod)) {
            return $collectionMethod($collection);
        }

        return $collection;
    }

    /**
     * Returns parameter value based by it's name
     *
     * @param string $name Parameter name.
     * @return mixed
     */
    public function data(string $name = null)
    {
        if ($this->_Controller->getRequest()->is('get')) {
            if (empty($name)) {
                return $this->_Controller->getRequest()->getQueryParams();
            } else {
                return $this->_Controller->getRequest()->getQuery($name);
            }
        } elseif ($this->_Controller->getRequest()->is(['post', 'put'])) {
            if (empty($name)) {
                return $this->_Controller->getRequest()->getData($this->formName);
            } else {
                return $this->_Controller->getRequest()->getData($this->fieldName($name));
            }
        }

        return null;
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function values(): array
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
    public function viewValues(): array
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
    public function config(array $options): void
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
    public function fieldName(string $name): string
    {
        if (empty($this->formName)) {
            return $name;
        }

        return $this->formName . '.' . $name;
    }

    /**
     * Returns controller instance.
     *
     * @return \Cake\Controller\Controller
     */
    public function controller(): \Cake\Controller\Controller
    {
        return $this->_Controller;
    }

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [];
    }

    /**
     * Override to allow serialization
     * @return array
     */
    public function __sleep(): array
    {
        return ['formName'];
    }
}
