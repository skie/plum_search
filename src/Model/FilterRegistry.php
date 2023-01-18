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
namespace PlumSearch\Model;

use Cake\Collection\CollectionInterface;
use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use Cake\ORM\Table;
use PlumSearch\Model\Filter\AbstractFilter;
use PlumSearch\Model\Filter\Exception\MissingFilterException;

/**
 * FilterRegistry is a registry for loaded filters
 *
 * Handles loading, constructing  for filter class objects.
 *
 * @extends \Cake\Core\ObjectRegistry<\PlumSearch\Model\Filter\AbstractFilter>
 */
class FilterRegistry extends ObjectRegistry
{
    /**
     * The table that this collection was initialized with.
     */
    protected \Cake\ORM\Table $_Table;

    /**
     * Constructor.
     *
     * @param \Cake\ORM\Table $Table Table instance.
     */
    public function __construct(Table $Table)
    {
        $this->_Table = $Table;
    }

    /**
     * Resolve a filter class name.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param  string       $class Partial class name to resolve.
     * @return string|null Either the correct class name or false.
     */
    protected function _resolveClassName($class): ?string
    {
        $result = App::className($class, 'Model/Filter', 'Filter');
        if ($result || strpos($class, '.') !== false) {
            return $result;
        }

        return App::className('PlumSearch.' . $class, 'Model/Filter', 'Filter');
    }

    /**
     * Throws an exception when a filter is missing.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param  string                                                    $class  The class name that is missing.
     * @param  string                                                    $plugin The plugin the filter is missing in.
     * @return void
     * @throws \PlumSearch\Model\Filter\Exception\MissingFilterException
     */
    protected function _throwMissingClassError(string $class, ?string $plugin): void
    {
        throw new MissingFilterException([
            'class' => $class . 'Filter',
            'plugin' => $plugin,
        ]);
    }

    /**
     * Create the filter instance.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     * Enabled filters will be registered with the event manager.
     *
     * @param  string $class The class name to create.
     * @param  string $alias The alias of the filter.
     * @param  array $config An array of config to use for the filter.
     * @return \PlumSearch\Model\Filter\AbstractFilter The constructed filter class.
     */
    protected function _create($class, string $alias, array $config): AbstractFilter
    {
        if (empty($config['name'])) {
            $config['name'] = $alias;
        }

        return new $class($this, $config);
    }

    /**
     * Return collection of loaded filters
     *
     * @return \Cake\Collection\CollectionInterface
     */
    public function collection(): CollectionInterface
    {
        return collection($this->_loaded);
    }
}
