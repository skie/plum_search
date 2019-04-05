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
use PlumSearch\Model\Filter\MultipleFilter;

/**
 * PlumSearch\Model\Filter\MultipleFilter Test Case
 */
class MultipleFilterTest extends TestCase
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
     * Data providor for testApply method
     *
     * @return array
     */
    public function applyDataProvider()
    {
        return [
            [false, 'OR'],
            [MultipleFilter::TYPE_OR, 'OR'],
            [MultipleFilter::TYPE_AND, 'AND'],
        ];
    }

    /**
     * Test apply method
     *
     * @dataProvider applyDataProvider
     * @return void
     */
    public function testApply($type, $operator)
    {
        $options = [
            'name' => 'name',
            'fields' => ['title', 'body']
        ];

        if ($type !== false) {
            $options['type'] = $type;
        }

        $this->MultipleFilter = new MultipleFilter($this->FilterRegistry, $options);

        $query = $this->Table->find('all');
        $this->MultipleFilter->apply($query, ['name' => 'test']);
        $store = null;
        $query->traverse(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);
        $store2 = null;
        $store->traverse(function ($d) use (&$store2) {
            $store2 = $d;
        }, ['where']);
        $this->assertEquals(__('(title LIKE :c0 {0} body LIKE :c1)', $operator), $store->sql($query->getValueBinder()));
        $this->assertEquals('%test%', $store2->getValue());
    }
}
