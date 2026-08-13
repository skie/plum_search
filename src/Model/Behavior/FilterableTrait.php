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
namespace PlumSearch\Model\Behavior;

use Cake\ORM\Table;

/**
 * Class FilterableBehavior
 *
 * @package PlumSearch\Model\Behavior
 * @phpstan-ignore trait.unused
 */
trait FilterableTrait
{
    /**
     * Get the filters
     *
     * @return \PlumSearch\Model\FilterRegistry
     */
    public function filters(): \PlumSearch\Model\FilterRegistry
    {
        return $this->getBehavior('Filterable')->filters();
    }

    /**
     * Add a filter
     *
     * @param string $name The name of the filter
     * @param array $params The parameters for the filter
     * @return \Cake\ORM\Table
     */
    public function addFilter(string $name, array $params = []): Table
    {
        return $this->getBehavior('Filterable')->addFilter($name, $params);
    }

    /**
     * Remove a filter
     *
     * @param string $name The name of the filter
     * @return \Cake\ORM\Table
     */
    public function removeFilter(string $name): Table
    {
        return $this->getBehavior('Filterable')->removeFilter($name);
    }
}
