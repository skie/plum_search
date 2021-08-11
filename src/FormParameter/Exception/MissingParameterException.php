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
namespace PlumSearch\FormParameter\Exception;

use Cake\Core\Exception\CakeException;

/**
 * Missing Param exception - used when a form parameter
 * cannot be found.
 */
class MissingParameterException extends CakeException
{
    /**
     * @inheritDoc
     */
    protected $_messageTemplate = 'Filter %s::%s() could not be found.';

    /**
     * @inheritDoc
     */
    public function __construct($message, int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
