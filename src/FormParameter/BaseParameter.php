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
namespace PlumSearch\FormParameter;

use Cake\Core\InstanceConfigTrait;

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
     */
    protected \PlumSearch\FormParameter\ParameterRegistry $_registry;

    /**
     * List of dependent parameters
     *
     * @var \PlumSearch\FormParameter\BaseParameter[]
     */
    protected array $_dependentParameters = [];

    /**
     * Parameter value
     *
     * @var mixed
     */
    public $value;

    /**
     * Process flag
     */
    protected bool $_processed = false;

    /**
     * Constructor
     *
     * @param \PlumSearch\FormParameter\ParameterRegistry $registry ParameterRegistry object.
     * @param array $config Object settings.
     */
    public function __construct(ParameterRegistry $registry, array $config = [])
    {
        if (empty($config['field'])) {
            $config['field'] = $config['name'];
        }
        $this->setConfig($config);
        $this->_registry = $registry;
        $this->initializeInnerParameters();
    }

    /**
     * Defines if parameter visible in form or this is hidden parameter.
     *
     * @return bool
     */
    public function visible(): bool
    {
        $visible = $this->getConfig('visible');

        return !empty($visible);
    }

    /**
     * Returns input config
     *
     * @return array
     */
    public function formInputConfig(): array
    {
        $formConfig = $this->getConfig('formConfig');
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
    protected function _process(): void
    {
        $name = $this->getConfig('field');
        $this->value = $this->_registry->data($name);
        $this->_processed = true;
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function values(): array
    {
        $name = $this->getConfig('field');

        return [$name => $this->value()];
    }

    /**
     * Build view values list
     *
     * @return \PlumSearch\FormParameter\BaseParameter[]
     */
    public function viewValues(): array
    {
        return array_merge([$this->getConfig('field') => $this], $this->_dependentParameters);
    }

    /**
     * initialize inner parameters
     *
     * @return void
     */
    public function initializeInnerParameters(): void
    {
        $this->_dependentParameters = [];
    }

    /**
     * Returns if parameter provides multiple options
     *
     * @return bool
     */
    public function hasOptions(): bool
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

    /**
     * Checks if param value empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->value === null || is_string($this->value) && $this->value == '';
    }

    /**
     * Transforms class to string value. Returns stored parameter value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
