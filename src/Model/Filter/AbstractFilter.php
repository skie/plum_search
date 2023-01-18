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
namespace PlumSearch\Model\Filter;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Query\SelectQuery;
use PlumSearch\Model\Filter\Exception\MissingFilterException;
use PlumSearch\Model\FilterRegistry;

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
     */
    protected array $_defaultConfig = [];

    /**
     * FilterRegistry storage.
     */
    protected \PlumSearch\Model\FilterRegistry $registry;

    /**
     * Filter constructor
     *
     * @param \PlumSearch\Model\FilterRegistry $registry FilterRegistry instance.
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
     * @param  \Cake\ORM\Query\SelectQuery $query Query.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function apply(SelectQuery $query, array $data): SelectQuery
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

        return $field && (
                !empty($data[$field]) ||
                $this->_defaultDefined() ||
                isset($data[$field]) && (string)$data[$field] !== ''
            );
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
     * @param  \Cake\ORM\Query\SelectQuery $query Query.
     * @param string $field Field name.
     * @param string|array $value Field value.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query\SelectQuery
     */
    abstract protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery;

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
