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
namespace PlumSearch\Model\Filter;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Query;
use PlumSearch\Model\FilterRegistry;
use PlumSearch\Model\Filter\Exception\MissingFilterException;

/**
 * Class AbstractFilter
 *
 * @package PlumSearch\Model\Filter
 */
abstract class AbstractFilter
{
    use InstanceConfigTrait;

    /**
     * Default configuration
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * FilterRegistry storage.
     *
     * @var FilterRegistry
     */
    protected $registry;

    /**
     * Filter constructor
     *
     * @param FilterRegistry $registry FilterRegistry instance.
     * @param array $config Filter configuration.
     * @throws \PlumSearch\Model\Filter\Exception\MissingFilterException Used when required options not defined.
     */
    public function __construct(FilterRegistry $registry, array $config = [])
    {
        if (empty($config['name'])) {
            throw new MissingFilterException(
                __('Missed "name" configuration setting for filter')
            );
        }
        if (empty($config['field'])) {
            $config['field'] = $config['name'];
        }
        $this->registry = $registry;
        $this->setConfig($config);
    }

    /**
     * Apply filter to query based on filter data
     *
     * @param  \Cake\ORM\Query $query Query.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query
     */
    public function apply(Query $query, array $data): \Cake\ORM\Query
    {
        if ($this->_applicable($data)) {
            $field = $this->getConfig('field');
            if (is_string($field) && (strpos($field, '.') === false)) {
                $field = $query->getRepository()->getAlias() . '.' . $field;
            }

            return $this->_buildQuery($query, $field, $this->_value($data), $data);
        }

        return $query;
    }

    /**
     * Check if filter applicable to query based on filter data
     *
     * @param array $data Array of options as described above.
     * @return bool
     */
    protected function _applicable(array $data): bool
    {
        $field = $this->getConfig('name');

        return $field && (!empty($data[$field]) || $this->_defaultDefined() || isset($data[$field]) && (string)$data[$field] !== '');
    }

    /**
     * Checks if default setting is set.
     *
     * @return bool
     */
    protected function _defaultDefined(): bool
    {
        $default = $this->getConfig('default');

        return !empty($default);
    }

    /**
     * Returns query with applied filter
     *
     * @param  \Cake\ORM\Query $query Query.
     * @param string $field Field name.
     * @param string|array $value Field value.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query
     */
    abstract protected function _buildQuery(Query $query, string $field, $value, array $data = []): \Cake\ORM\Query;

    /**
     * Evaluate value of filter parameter
     *
     * @param array $data Array of options as described above.
     * @return mixed
     */
    protected function _value(array $data)
    {
        $field = $this->getConfig('name');
        $value = $data[$field];
        if (empty($value) && $this->_defaultDefined()) {
            $value = $this->getConfig('default');
        }

        return $value;
    }
}
