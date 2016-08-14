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
namespace PlumSearch\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Model;
use PlumSearch\Model\FilterRegistry;
use PlumSearch\Model\Filter\LikeFilter;

/**
 * PlumSearch\Model\Filter\LikeFilter Test Case
 */
class LikeFilterTest extends TestCase
{
    public $fixtures = ['plugin.plum_search.articles'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Articles');
        $this->FilterRegistry = new FilterRegistry($this->Table);
        $this->LikeFilter = new LikeFilter($this->FilterRegistry, [
            'name' => 'name'
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->LikeFilter);
        parent::tearDown();
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApply()
    {
        $query = $this->Table->find('all');
        $this->LikeFilter->apply($query, ['name' => 'test']);
        $store = null;
        $query->traverse(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);
        $store2 = null;
        $store->traverse(function ($d) use (&$store2) {
            $store2 = $d;
        }, ['where']);

        $this->assertEquals($store2->sql($query->valueBinder()), 'Articles.name LIKE :c0');
        $this->assertEquals($store2->getValue(), '%test%');
    }
}
