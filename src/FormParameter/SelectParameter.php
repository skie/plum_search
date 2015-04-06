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

use Cake\ORM\Query;
use PlumSearch\FormParameter\Exception\MissingParameterException;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class SelectParam
 *
 * @package PlumSearch\FormParameter
 */
class SelectParameter extends BaseParameter
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'visible' => true,
        'formConfig' => [
            'type' => 'select',
        ],
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
        if (empty($config['options']) || !is_array($config['options'])) {
            if (empty($config['finder'])) {
                throw new MissingParameterException(
                    __('Missed "finder" configuration setting for select param `{0}`', $this->config('name'))
                );
            }
        }
    }

    /**
     * Returns if parameter provides multiple options
     *
     * @return bool
     */
    public function hasOptions()
    {
        return true;
    }

    /**
     * Returns input config
     *
     * @return array
     */
    public function formInputConfig()
    {
        $formConfig = parent::formInputConfig();

        if (!array_key_exists('options', $formConfig)) {
            $options = $this->config('options');
            $finder = $this->config('finder');
            if (!empty($options) && is_array($options)) {
                $formConfig['options'] = $options;
            } elseif (!empty($finder)) {
                $formConfig['options'] = $finder;
            }
        }

        return $formConfig;
    }
}
