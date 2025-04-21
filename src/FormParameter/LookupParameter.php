<?php
declare(strict_types=1);

namespace PlumSearch\FormParameter;

use Cake\Routing\Router;

/**
 * Class LookupParameter
 *
 * A custom parameter for autocomplete lookups using existing endpoints
 *
 * @package App\FormParameter
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
        $config['class'] = 'lookup-autocomplete';

        return $config;
    }
}