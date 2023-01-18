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

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query\SelectQuery;

class LikeFilter extends AbstractFilter
{
    /**
     * Returns query with applied filter
     *
     * @param  \Cake\ORM\Query\SelectQuery $query Query.
     * @param  string $field Field name.
     * @param  string $value Field value.
     * @param  array  $data Filters values.
     * @return \Cake\ORM\Query\SelectQuery
     */
    protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery
    {
        $value = '%' . $value . '%';

        return $query->where(
            fn(QueryExpression $exp): QueryExpression => $exp->like($field, $value)
        );
    }
}
