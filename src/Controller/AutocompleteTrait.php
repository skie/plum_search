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
namespace PlumSearch\Controller;

/**
 * Class AutocompleteTrait
 * Implements generic autocomplete logic
 *
 * @package PlumSearch\Controller
 */
trait AutocompleteTrait
{
    /**
     * Autocomplete action
     * Requires query arguments:
     * - string $paramName Parameter name.
     * - string $query Search query.
     *
     * @return void
     */
    public function autocomplete(): void
    {
        $this->viewBuilder()->setClassName('Json');
        $data = [];
        $paramName = (string)$this->getRequest()->getQuery('parameter');
        $query = $this->getRequest()->getQuery('query');
        try {
            $parameter = $this->Filter->parameters()->get($paramName);
        } catch (\Exception $e) {
            $parameter = null;
        }
        if (!empty($parameter)) {
            $method = $parameter->getConfig('autocompleteAction');
            $data = $method($query);
            $this->set('status', 'success');
        } else {
            $this->set('status', 'error');
            $this->set('message', __('Field not found'));
        }
        $this->set('data', $data);
        $this->viewBuilder()->setOption('serialize', ['data', 'status', 'message']);
    }
}
