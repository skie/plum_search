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
namespace PlumSearch\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Model\FilterRegistry;
use PlumSearch\Model\Filter\ValueFilter;
use PlumSearch\Test\App\Model\Table\ArticlesTable;

/**
 * PlumSearch\Model\Behavior\FilterBehavior Test Case
 */
class FilterBehaviorTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.plum_search.articles',
        'plugin.plum_search.articles_tags',
        'plugin.plum_search.tags',
        'plugin.plum_search.authors',
    ];

    /**
     * @var ArticlesTable
     */
    public $Articles;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Articles');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
    }

    /**
     * Test filters method
     *
     * @return void
     */
    public function testFilters()
    {
        $filter = $this->Articles->filters();
        $this->assertTrue($filter instanceof FilterRegistry);
    }

    /**
     * Test addFilter method
     *
     * @return void
     */
    public function testAddFilter()
    {
        $this->Articles->addFilter('name', ['className' => 'Value']);
        $input = $this->Articles->filters()->get('name');
        $this->assertTrue($input instanceof ValueFilter);
    }

    /**
     * Test removeFilter method
     *
     * @return void
     */
    public function testRemoveFilter()
    {
        $this->Articles->addFilter('name', ['className' => 'Value']);
        $this->Articles->removeFilter('name');
        $input = $this->Articles->filters()->get('name');
        $this->assertNull($input);
    }

    /**
     * Test findFilter method
     *
     * @return void
     */
    public function testFindFilter()
    {
        $result = $this->Articles->find('filters', [])->hydrate(false)->toArray();
        $this->assertEquals(count($result), 3);

        $filterParameters = [
            'title' => 'first',
            'tag' => 'tag1',
        ];
        $result = $this->Articles->find('filters', $filterParameters)->hydrate(false)->toArray();
        $expected = [[
            'id' => 1,
            'author_id' => 1,
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 'Y',
        ]];
        $this->assertEquals($expected, $result);

        $filterParameters = [
            'title' => 'first',
            'tag' => 'tag3',
        ];
        $result = $this->Articles->find('filters', $filterParameters)->hydrate(false)->toArray();
        $expected = [];
        $this->assertEquals($expected, $result);

        $this->Articles->addFilter('author_name', [
            'className' => 'Value',
            'field' => 'Authors.name'
        ]);
        $filterParameters = [
            'author_name' => 'larry',
            'tag' => 'tag3',
        ];
        $result = $this->Articles->find('withAuthors')->find('filters', $filterParameters)->hydrate(false)->toArray();
        $expected = [[
            'id' => 2,
            'author_id' => 3,
            'title' => 'Second Article',
            'body' => 'Second Article Body',
            'published' => 'Y',
            '_matchingData' => [
                'Authors' => [
                    'id' => 3,
                    'name' => 'larry',
                ],
            ],
        ]];
        $this->assertEquals($expected, $result);
    }
}
