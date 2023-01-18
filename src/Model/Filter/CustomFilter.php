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
use PlumSearch\Model\Filter\Exception\MissingFilterException;
use PlumSearch\Model\FilterRegistry;

/**
 * Class CustomFilter
 *
 * @package PlumSearch\Model\Filter
 */
class CustomFilter extends AbstractFilter
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
        parent::__construct($registry, $config);
        if (empty($config['method']) || !is_callable($config['method'])) {
            throw new MissingFilterException(
                __('Missed "method" configuration setting for custom filter `{0}`', $this->getConfig('name'))
            );
        }
    }

    /**
     * Returns query with applied filter
     *
     * @param  \Cake\ORM\Query\SelectQuery $query Query.
     * @param  string $field Field name.
     * @param string|array $value Field value.
     * @param  array $data Filters values.
     * @return \Cake\ORM\Query\SelectQuery
     */
    protected function _buildQuery(SelectQuery $query, string $field, $value, array $data = []): SelectQuery
    {
        $method = $this->getConfig('method');
        if (is_callable($method)) {
            return call_user_func_array($method, [$query, $field, $value, $data, $this->getConfig()]);
        }

        return $query;
    }
}
