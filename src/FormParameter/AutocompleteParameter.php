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

use Cake\Routing\Router;
use PlumSearch\FormParameter\Exception\MissingParameterException;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class AutocompleteParam
 *
 * @package PlumSearch\FormParameter
 */
class AutocompleteParameter extends BaseParameter
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'visible' => true,
    ];

    /**
     * Constructor
     *
     * @param ParameterRegistry $registry ParameterRegistry object.
     * @param array $config Object settings.
     * @throws \PlumSearch\FormParameter\Exception\MissingParameterException
     */
    public function __construct(ParameterRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $config['field'] = $config['name'] . '_lookup';
        $this->setConfig($config);
        if (empty($config['autocompleteAction']) || !is_callable($config['autocompleteAction'])) {
            throw new MissingParameterException(
                __('Missed "autocompleteAction" configuration setting for select param `{0}`', $this->getConfig('name'))
            );
        }
    }

    /**
     * Builds autocomplete url
     * @todo allow override url with parameter configuration
     *
     * @return string
     */
    public function autocompleteUrl()
    {
        $request = $this->_registry->controller()->getRequest();

        return Router::url([
            'controller' => $request->getParam('controller'),
            'action' => 'autocomplete',
        ]);
    }

    /**
     * initialize inner parameters
     *
     * @return void
     */
    public function initializeInnerParameters()
    {
        $paramName = $this->getConfig('name');
        $this->_dependentParameters[$paramName] = new HiddenParameter($this->_registry, [
            'name' => $paramName
        ]);
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function values()
    {
        $name = $this->getConfig('field');
        $paramName = $this->getConfig('name');
        $param = $this->_dependentParameters[$paramName];

        return [
            $name => $this->value(),
            $paramName => $param->value()
        ];
    }
}
