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
namespace PlumSearch\Test\TestCase\Model\Filter;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Model\Filter\CustomFilter;
use PlumSearch\Model\Filter\Exception\MissingFilterException;
use PlumSearch\Model\FilterRegistry;

/**
 * PlumSearch\Model\Filter\CustomFilter Test Case
 */
class CustomFilterTest extends TestCase
{
    public $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

    /**
     * @var Table
     */
    protected $Table;

    /**
     * @var FilterRegistry
     */
    protected $FilterRegistry;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Articles');
        $this->FilterRegistry = new FilterRegistry($this->Table);
        $this->CustomFilter = new CustomFilter($this->FilterRegistry, [
            'name' => 'id',
            'method' => function ($query, $data): Query {
                return $query;
            },
        ]);
    }

    /**
     * @var CustomFilter
     */
    protected $CustomFilter;

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CustomFilter);
        parent::tearDown();
    }

    /**
     * Test constructor method
     *
     * @return void
     */
    public function testConstruct()
    {
        $this->expectException(MissingFilterException::class);
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
            'method' => function ($query, $field, $value, $data, $config): Query {
                return $query
                    ->where([
                        'OR' => [
                            'title LIKE' => $value,
                            'decription LIKE ' => $value,
                        ],
                    ]);
            },
        ]);

        $query = $this->Table->find('all');
        $this->CustomFilter->apply($query, ['id' => 1]);

        $this->assertRegExp('/WHERE \(title like :c0 OR decription like :c1\)/', $query->sql());
    }
}
