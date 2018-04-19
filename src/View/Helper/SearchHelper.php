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
namespace PlumSearch\View\Helper;

use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use PlumSearch\FormParameter\AutocompleteParameter;
use PlumSearch\FormParameter\BaseParameter;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class SearchHelper
 *
 * @package PlumSearch\View\Helper
 */
class SearchHelper extends Helper
{

    /**
     * Build Form::inputs structure
     *
     * @param ParameterRegistry $parameters Form parameters collection.
     * @param array $options Additional input options.
     * @return array
     */
    public function inputs(ParameterRegistry $parameters, $options = [])
    {
        $result = [];
        $entityName = Inflector::singularize($parameters->formName);
        $collection = $parameters->collection(isset($options['collectionMethod']) ? $options['collectionMethod'] : null);
        foreach ($collection as $primaryParameter) {
            foreach ($primaryParameter->viewValues() as $param) {
                $name = $param->getConfig('name');
                $inputOptions = array_key_exists($name, $options) ? $options[$name] : [];
                $input = $this->input($param, $inputOptions);
                $field = $param->getConfig('field');
                if (!empty($entityName)) {
                    $field = "$entityName.$field";
                }
                $result[$field] = $input;
            }
        }

        return $result;
    }

    /**
     * Generates input for parameter
     *
     * @param BaseParameter $param Form parameter.
     * @param array $options Additional input options.
     * @return array
     */
    public function input(BaseParameter $param, $options = [])
    {
        $input = $this->_defaultInput($param);
        $this->_setValue($input, $param);
        $this->_setOptions($input, $param);
        $this->_applyAutocompleteOptions($input, $param);
        $input = Hash::merge($input, $options);

        return $input;
    }

    /**
     * Generates default input for parameter
     *
     * @param BaseParameter $param Form parameter.
     * @return array
     */
    protected function _defaultInput($param)
    {
        $input = $param->formInputConfig();
        $name = $param->getConfig('name');
        $input += [
            'type' => 'text',
            'required' => false,
            'label' => Inflector::humanize(preg_replace('/_id$/', '', $name)),
        ];
        if (!$param->visible()) {
            $input['type'] = 'hidden';
        }

        return $input;
    }

    /**
     * Set value for field
     *
     * @param array $input Field options.
     * @param BaseParameter $param Form parameter.
     * @return array
     */
    protected function _setValue(&$input, $param)
    {
        $value = $param->value();
        if (!$param->isEmpty()) {
            $input['value'] = $value;
        } else {
            $input['value'] = '';
        }

        return $input;
    }

    /**
     * Set options for field
     *
     * @param array $input Field options.
     * @param BaseParameter $param Form parameter.
     * @return array
     */
    protected function _setOptions(&$input, $param)
    {
        if ($param->hasOptions()) {
            $input['empty'] = true;
        }

        return $input;
    }

    /**
     * Set autocomplete settings for field
     *
     * @param array $input Field options.
     * @param BaseParameter $param Form parameter.
     * @return array
     */
    protected function _applyAutocompleteOptions(&$input, $param)
    {
        if ($param instanceof AutocompleteParameter) {
            $input['data-url'] = $param->autocompleteUrl();
            $input['class'] = 'autocomplete';
            $input['data-name'] = $param->getConfig('name');
        }

        return $input;
    }
}
