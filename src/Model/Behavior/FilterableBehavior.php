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
namespace PlumSearch\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use PlumSearch\Model\FilterRegistry;

/**
 * Class FilterableBehavior
 *
 * @package PlumSearch\Model\Behavior
 */
class FilterableBehavior extends Behavior
{
    /**
     * FilterRegistry for this table
     *
     * @var \PlumSearch\Model\FilterRegistry
     */
    protected $_searchFilters = null;

    /**
     * Table instance
     *
     * @var \Cake\ORM\Table
     */
    protected $_table;

    /**
     * Default config
     *
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'implementedFinders' => [
            'filters' => 'findFilter',
        ],
        'implementedMethods' => [
            'filters' => 'filters',
            'addFilter' => 'addFilter',
            'removeFilter' => 'removeFilter',
        ],
    ];

    /**
     * Constructor
     *
     * @param \Cake\ORM\Table $table The table this behavior is attached to.
     * @param array $config The config for this behavior.
     */
    public function __construct(Table $table, array $config = [])
    {
        parent::__construct($table, $config);
        $this->_table = $table;
        $this->filters();
    }

    /**
     * Returns filter registry instance
     *
     * @param  bool $reset Reset flag.
     * @return \PlumSearch\Model\FilterRegistry
     */
    public function filters($reset = false)
    {
        if ($reset || is_null($this->_searchFilters)) {
            $this->_searchFilters = new FilterRegistry($this->_table);
        }

        return $this->_searchFilters;
    }

    /**
     * Add a filter.
     *
     * Adds a filter to this table's filter collection. Filters
     * provide an rules how search process should filter data.
     *
     * Example:
     *
     * Load a filter, with some settings.
     *
     * {{{
     * $this->addFilter('Name', ['className' => 'Like']);
     * }}}
     *
     * Filters are generally loaded during Table::initialize().
     *
     * @param string $name The name of the filter.
     * @param array $options The options for the filter to use.
     * @return \Cake\ORM\Table
     */
    public function addFilter($name, array $options = [])
    {
        $this->filters()->load($name, $options);

        return $this->_table;
    }

    /**
     * Removes a filter from this table's filter registry.
     *
     * Example:
     *
     * Remove a filter from this table.
     *
     * {{{
     * $this->removeFilter('Name');
     * }}}
     *
     * @param string $name The alias that the filter was added with.
     * @return \Cake\ORM\Table
     */
    public function removeFilter($name)
    {
        $this->filters()->unload($name);

        return $this->_table;
    }

    /**
     * Results for this finder will be query filtered by search parameters
     *
     * @param \Cake\ORM\Query $query   Query.
     * @param array $options Array of options as described above.
     * @return \Cake\ORM\Query
     */
    public function findFilter(Query $query, array $options)
    {
        foreach ($this->filters()->collection() as $name => $filter) {
            $filter->apply($query, $options);
        }

        return $query;
    }
}
