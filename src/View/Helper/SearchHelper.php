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
     * Builds Form::controls structure.
     *
     * @param \PlumSearch\FormParameter\ParameterRegistry $parameters Form parameters collection.
     * @param array $options Additional input options.
     * @return array
     */
    public function controls(ParameterRegistry $parameters, array $options = []): array
    {
        $result = [];
        $entityName = Inflector::singularize($parameters->getFormName());
        $collection = $parameters->collection($options['collectionMethod'] ?? null);
        foreach ($collection as $primaryParameter) {
            foreach ($primaryParameter->viewValues() as $param) {
                $name = $param->getConfig('name');
                $inputOptions = array_key_exists($name, $options) ? $options[$name] : [];
                $input = $this->control($param, $inputOptions);
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
     * Executes Parameter::postRender callbacks if presents.
     *
     * @param \PlumSearch\FormParameter\ParameterRegistry $parameters Form parameters collection.
     * @param array $options Additional input options.
     * @return string
     */
    public function postRender(ParameterRegistry $parameters, array $options = []): string
    {
        $result = '';
        $collection = $parameters->collection($options['collectionMethod'] ?? null);
        foreach ($collection as $primaryParameter) {
            $callback = $primaryParameter->getConfig('postRenderCallback');
            if (is_callable($callback)) {
                $result .= $callback($primaryParameter, $this->_View);
            }
        }

        return $result;
    }

    /**
     * Generates control for parameter
     *
     * @param \PlumSearch\FormParameter\BaseParameter $param Form parameter.
     * @param array $options Additional input options.
     * @return array
     */
    public function control(BaseParameter $param, array $options = []): array
    {
        $input = $this->_defaultControl($param);
        $this->_setValue($input, $param);
        $this->_setOptions($input, $param);
        $this->_applyAutocompleteOptions($input, $param);

        return Hash::merge($input, $options);
    }

    /**
     * Generates input for parameter
     *
     * @param \PlumSearch\FormParameter\BaseParameter $param Form parameter.
     * @param array $options Additional input options.
     * @return array
     * @deprecated 4.3.0 Use SearchHelper::control() instead.
     */
    public function input(BaseParameter $param, array $options = []): array
    {
        deprecationWarning(
            'SearchHelper::input() is deprecated. Use SearchHelper::control() instead.'
        );

        return $this->control($param, $options);
    }

    /**
     * Generates default control for parameter
     *
     * @param \PlumSearch\FormParameter\BaseParameter $param Form parameter.
     * @return array
     */
    protected function _defaultControl(BaseParameter $param): array
    {
        $input = $param->formInputConfig();
        $name = (string)$param->getConfig('name');
        $input += [
            'type' => 'text',
            'required' => false,
            'label' => Inflector::humanize((string)preg_replace('/_id$/', '', $name)),
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
     * @param \PlumSearch\FormParameter\BaseParameter $param Form parameter.
     * @return array
     */
    protected function _setValue(array &$input, BaseParameter $param): array
    {
        $value = $param->value();
        $input['value'] = $param->isEmpty() ? '' : $value;

        return $input;
    }

    /**
     * Set options for field
     *
     * @param array $input Field options.
     * @param \PlumSearch\FormParameter\BaseParameter $param Form parameter.
     * @return array
     */
    protected function _setOptions(array &$input, BaseParameter $param): array
    {
        if ($param->hasOptions() && !isset($input['empty'])) {
            $input['empty'] = true;
        }

        return $input;
    }

    /**
     * Set autocomplete settings for field
     *
     * @param array $input Field options.
     * @param \PlumSearch\FormParameter\BaseParameter $param Form parameter.
     * @return array
     */
    protected function _applyAutocompleteOptions(array &$input, BaseParameter $param): array
    {
        if ($param instanceof AutocompleteParameter) {
            $input['data-url'] = $param->autocompleteUrl();
            $input['class'] = 'autocomplete';
            $input['data-name'] = $param->getConfig('name');
        }

        return $input;
    }
}
