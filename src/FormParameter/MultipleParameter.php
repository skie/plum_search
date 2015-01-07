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

/**
 * Class MultipleParam
 *
 * @package PlumSearch\FormParameter
 */
class MultipleParam extends SelectParameter
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
            'multiple' => 'checkbox',
        ],
    ];
}
