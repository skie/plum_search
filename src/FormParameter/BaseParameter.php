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

use Cake\Core\InstanceConfigTrait;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class BaseParam
 *
 * @package PlumSearch\FormParameter
 */
abstract class BaseParameter
{
    use InstanceConfigTrait;

    /**
     * Default configuration
     *
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * ParameterRegistry instance
     *
     * @var ParameterRegistry
     */
    protected $_registry;

    /**
     * List of dependent parameters
     *
     * @var BaseParameter[]
     */
    protected $_dependentParameters = [];

    /**
     * Parameter value
     *
     * @var mixed
     */
    public $value;

    /**
     * Process flag
     *
     * @var bool
     */
    protected $_processed = false;

    /**
     * Constructor
     *
     * @param ParameterRegistry $registry ParameterRegistry object.
     * @param array $config Object settings.
     */
    public function __construct(ParameterRegistry $registry, array $config = [])
    {
        if (empty($config['field'])) {
            $config['field'] = $config['name'];
        }
        $this->config($config);
        $this->_registry = $registry;
        $this->initializeInnerParameters();
    }

    /**
     * Defines if parameter visible in form or this is hidden parameter.
     *
     * @return bool
     */
    public function visible()
    {
        $visible = $this->config('visible');

        return !empty($visible);
    }

    /**
     * Returns input config
     *
     * @return array
     */
    public function formInputConfig()
    {
        $formConfig = $this->config('formConfig');
        if (empty($formConfig)) {
            return [];
        }

        return $formConfig;
    }

    /**
     * process param parsing
     *
     * @return void
     */
    protected function _process()
    {
        $name = $this->config('field');
        $this->value = $this->_registry->data($name);
        $this->_processed = true;
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function values()
    {
        $name = $this->config('field');

        return [$name => $this->value()];
    }

    /**
     * Build view values list
     *
     * @return BaseParameter[]
     */
    public function viewValues()
    {
        return array_merge([$this->config('field') => $this], $this->_dependentParameters);
    }

    /**
     * initialize inner parameters
     *
     * @return void
     */
    public function initializeInnerParameters()
    {
        $this->_dependentParameters = [];
    }

    /**
     * Returns if parameter provides multiple options
     *
     * @return bool
     */
    public function hasOptions()
    {
        return false;
    }

    /**
     * Returns parameter value
     *
     * @return mixed
     */
    public function value()
    {
        if (!$this->_processed) {
            $this->_process();
        }

        return $this->value;
    }
}
