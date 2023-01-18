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
namespace PlumSearch\Test\App\Model\Filter;

use Cake\Database\Expression\UnaryExpression;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use PlumSearch\Model\Filter\AbstractFilter;

class TagsFilter extends AbstractFilter
{
    /**
     * Returns query with applied filter
     *
     * @param \Cake\ORM\Query $query Query.
     * @param string $field Field name.
     * @param string $value Field value.
     * @param array $data Filters values.
     * @return \Cake\ORM\Query\SelectQuery
     */
    protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery
    {
        $idName = $query->getRepository()->aliasField('id');

        $tags = TableRegistry::getTableLocator()->get('ArticlesTags')->find('all')
        ->matching('Tags', fn($q) => $q->where([
            'Tags.name' => $value,
        ]))
        ->where([
            "ArticlesTags.article_id = $idName",
        ]);

        return $query
        ->where([new UnaryExpression('EXISTS', $tags)]);
    }
}
