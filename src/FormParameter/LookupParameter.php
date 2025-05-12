<?php
declare(strict_types=1);

/**
 * PlumSearch plugin for CakePHP Rapid Development Framework
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Evgeny Tomenko
 * @since         PlumSearch 5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace PlumSearch\FormParameter;

use Cake\Routing\Router;

/**
 * Class LookupParameter
 *
 * A custom parameter for autocomplete lookups using existing endpoints
 *
 * @package \PlumSearch\FormParameter\FormParameter
 */
class LookupParameter extends BaseParameter
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'visible' => true,
        'autocompleteUrl' => null,
        'idName' => 'id',
        'valueName' => 'name',
        'query' => 'search=%QUERY',
        'wildcard' => '%QUERY',
        'minLength' => 2,
        'delay' => 300,
        'parentField' => null,
        'parentIdParam' => null,
        'dependentFields' => [],
        'additionalParents' => [],
    ];

    /**
     * Constructor
     *
     * @param \PlumSearch\FormParameter\ParameterRegistry $registry ParameterRegistry object.
     * @param array $config Object settings.
     */
    public function __construct(\PlumSearch\FormParameter\ParameterRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $config['field'] = $config['name'] . '_lookup';
        $this->setConfig($config);

        if ($this->getConfig('parentField')) {
            $this->initializeParentField();
        }

        if (!empty($this->getConfig('additionalParents'))) {
            $this->initializeAdditionalParents();
        }
    }

    /**
     * Initialize parent field configuration
     *
     * @return void
     */
    protected function initializeParentField(): void
    {
        $parentField = $this->getConfig('parentField');
        $parentIdParam = $this->getConfig('parentIdParam');

        if (!$parentIdParam) {
            $parentIdParam = $parentField . '_id';
            $this->setConfig('parentIdParam', $parentIdParam);
        }
    }

    /**
     * Initialize additional parent fields
     *
     * @return void
     */
    protected function initializeAdditionalParents(): void
    {
        $additionalParents = $this->getConfig('additionalParents');
        foreach ($additionalParents as $field => $param) {
            if (!is_string($param)) {
                $additionalParents[$field] = $field . '_id';
            }
        }
        $this->setConfig('additionalParents', $additionalParents);
    }

    /**
     * Get autocomplete URL
     *
     * @return string
     */
    public function autocompleteUrl(): string
    {
        $url = $this->getConfig('autocompleteUrl');
        if (is_array($url)) {
            return Router::url($url);
        }
        return (string)$url;
    }

    /**
     * Initialize inner parameters
     *
     * @return void
     */
    public function initializeInnerParameters(): void
    {
        $paramName = $this->getConfig('name');
        $this->_dependentParameters[$paramName] = new \PlumSearch\FormParameter\HiddenParameter($this->_registry, [
            'name' => $paramName,
        ]);
    }

    /**
     * Build values list
     *
     * @return array
     */
    public function values(): array
    {
        $name = $this->getConfig('field');
        $paramName = $this->getConfig('name');
        $param = $this->_dependentParameters[$paramName];

        return [
            $name => $this->value(),
            $paramName => $param->value(),
        ];
    }

    /**
     * Get form input configuration
     *
     * @return array
     */
    public function formInputConfig(): array
    {
        $config = parent::formInputConfig();
        $config['data-id-name'] = $this->getConfig('idName');
        $config['data-value-name'] = $this->getConfig('valueName');
        $config['data-query'] = $this->getConfig('query');
        $config['data-wildcard'] = $this->getConfig('wildcard');
        $config['data-min-length'] = $this->getConfig('minLength');
        $config['data-delay'] = $this->getConfig('delay');
        $config['data-url'] = $this->autocompleteUrl();
        $config['data-parent-field'] = $this->getConfig('parentField');
        $config['data-parent-id-param'] = $this->getConfig('parentIdParam');
        $config['data-dependent-fields'] = json_encode($this->getConfig('dependentFields'));
        $config['data-additional-parents'] = json_encode($this->getConfig('additionalParents'));
        $config['class'] = 'lookup-autocomplete';

        return $config;
    }
}