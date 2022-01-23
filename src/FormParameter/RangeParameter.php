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

use Cake\Utility\Inflector;

/**
 * Class RangeParameter
 *
 * @package PlumSearch\FormParameter
 */
class RangeParameter extends BaseParameter
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'visible' => true,
        'baseSuffix' => '_from',
        'depSuffix' => '_to',
    ];

    /**
     * Constructor
     *
     * @param \PlumSearch\FormParameter\ParameterRegistry $registry ParameterRegistry object.
     * @param array $config Object settings.
     * @throws \PlumSearch\FormParameter\Exception\MissingParameterException
     */
    public function __construct(ParameterRegistry $registry, array $config = [])
    {
        $name = $config['name'];
        $suffix = $config['baseSuffix'] ?? $this->_defaultConfig['baseSuffix'];
        $config['formConfig']['label'] = $config['formConfig']['label'] ??
            Inflector::humanize((string)preg_replace('/_id$/', '', $name . $suffix));

        parent::__construct($registry, $config);
    }

    /**
     * initialize inner parameters
     *
     * @return void
     */
    public function initializeInnerParameters(): void
    {
        $paramName = $this->getConfig('name') . $this->getConfig('depSuffix');
        $this->_dependentParameters[$paramName] = new InputParameter($this->_registry, [
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
        $paramName = $this->getConfig('name') . $this->getConfig('depSuffix');
        $param = $this->_dependentParameters[$paramName];

        return [
            $name => $this->value(),
            $paramName => $param->value(),
        ];
    }
}
