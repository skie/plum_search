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

use Cake\ORM\Query\SelectQuery;
use PlumSearch\Model\FilterRegistry;

class RangeFilter extends AbstractFilter
{
    /**
     * Filter constructor
     *
     * @param \PlumSearch\Model\FilterRegistry $registry FilterRegistry instance.
     * @param array $config Filter configuration.
     * @throws \PlumSearch\Model\Filter\Exception\MissingFilterException Used when required options not defined.
     */
    public function __construct(FilterRegistry $registry, array $config = [])
    {
        if (empty($config['depSuffix'])) {
            $config['depSuffix'] = '_to';
        }
        parent::__construct($registry, $config);
    }

    /**
     * Returns query with applied filter
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query.
     * @param string $field Field name.
     * @param string|array $value Field value.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query\SelectQuery
     */
    protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery
    {
        $conditions = [];
        if ($this->_isFieldApplicable($this->getConfig('name'), $data)) {
            $conditions[$field . ' >='] = $value;
        }
        $depName = $this->getConfig('name') . $this->getConfig('depSuffix');
        if ($this->_isFieldApplicable($depName, $data)) {
            $conditions[$field . ' <='] = $data[$depName];
        }

        return $query->where($conditions);
    }

    /**
     * Check if filter applicable to query based on filter data
     *
     * @param array $data Array of options as described above.
     * @return bool
     */
    protected function _applicable(array $data): bool
    {
        return $this->_isFieldApplicable($this->getConfig('name'), $data) ||
           $this->_isFieldApplicable($this->getConfig('name') . '_to', $data);
    }

    /**
     * Check if field applicable to query based on filter data
     *
     * @param string $field Field name.
     * @param array $data Array of options as described above.
     * @return bool
     */
    private function _isFieldApplicable(string $field, array $data): bool
    {
        return $field && (
            !empty($data[$field]) ||
            $this->_defaultDefined() ||
            isset($data[$field]) && (string)$data[$field] !== ''
        );
    }
}
