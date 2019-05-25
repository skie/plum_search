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

use Cake\ORM\Query;

class ValueFilter extends AbstractFilter
{
    /**
     * Returns query with applied filter
     *
     * @param \Cake\ORM\Query $query Query.
     * @param string $field Field name.
     * @param string|array $value Field value.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query
     */
    protected function _buildQuery(Query $query, string $field, $value, array $data = []): \Cake\ORM\Query
    {
        if (is_array($value)) {
            $field .= ' IN';
        }

        return $query->where([$field => $value]);
    }
}
