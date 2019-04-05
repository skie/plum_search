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
use PlumSearch\Model\Filter\CustomFilter;

/**
 * PlumSearch\Model\Filter\CustomFilter Test Case
 */
class CustomFilterTest extends TestCase
{
    public $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

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
        $this->CustomFilter = new CustomFilter($this->FilterRegistry, [
            'name' => 'id',
            'method' => function ($query, $data) {
                return $query;
            }
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CustomFilter);
        parent::tearDown();
    }

    /**
     * Test constructor method
     *
     * @expectedException \PlumSearch\Model\Filter\Exception\MissingFilterException
     * @return void
     */
    public function testConstruct()
    {
        $this->CustomFilter = new CustomFilter($this->FilterRegistry, ['name' => 'id']);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApply()
    {
        $this->CustomFilter = new CustomFilter($this->FilterRegistry, [
            'name' => 'id',
            'method' => function ($query, $field, $value, $data, $config) {
                return $query
                    ->where([
                        'OR' => [
                            'title LIKE' => $value,
                            'decription LIKE ' => $value,
                        ],
                    ]);
            }
        ]);

        $query = $this->Table->find('all');
        $this->CustomFilter->apply($query, ['id' => 1]);

        $this->assertRegExp('/WHERE \(title like :c0 OR decription like :c1\)/', $query->sql());
    }
}
