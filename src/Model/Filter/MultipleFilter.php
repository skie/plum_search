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

use Cake\ORM\Query;
use PlumSearch\Model\FilterRegistry;
use PlumSearch\Model\Filter\AbstractFilter;

class MultipleFilter extends AbstractFilter
{
    /**
     * Constants for types
     */
    const TYPE_OR = 'or';
    const TYPE_AND = '';

    /**
     * Filter constructor
     *
     * @param FilterRegistry $registry FilterRegistry instance.
     * @param array $config Filter configuration.
     * @throws \PlumSearch\Model\Filter\Exception\MissingFilterException Used when required options not defined.
     */
    public function __construct(FilterRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        if (is_null($this->config('type')) || !in_array($this->config('type'), [self::TYPE_AND, self::TYPE_OR])) {
            $this->config('type', self::TYPE_OR);
        }
    }

    /**
     * Returns query with applied filter
     *
     * @param  \Cake\ORM\Query $query Query.
     * @param  string $field Field name.
     * @param  string $value Field value.
     * @param  array  $data Filters values.
     * @return \Cake\ORM\Query
     */
    protected function _buildQuery(Query $query, $field, $value, array $data = [])
    {
        $type = $this->config('type');
        $value = '%' . $value . '%';
        $fields = $this->config('fields');
        $typesMap = $this->config('fieldTypes');
        $types = [];
        foreach ($fields as $field) {
            if (is_array($typesMap) && array_key_exists($field, $typesMap)) {
                $types[$field] = $typesMap[$field];
            } else {
                $types[$field] = null;
            }
        }
        if (empty($type)) {
            $type = 'and';
        }

        return $query->where(function ($exp) use (&$query, $value, $type, $fields, $types) {
            return $exp->{$type . '_'}(function ($ex) use ($value, $fields, $types) {
                collection($fields)->each(function ($field) use ($value, &$ex, $types) {
                    return $ex->like($field, $value, $types[$field]);
                });

                return $ex;
            });
        });
    }
}
