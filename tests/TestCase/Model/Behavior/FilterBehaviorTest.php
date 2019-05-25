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
namespace PlumSearch\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Model\Filter\ValueFilter;
use PlumSearch\Model\FilterRegistry;
use RuntimeException;

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
        'plugin.PlumSearch.Articles',
        'plugin.PlumSearch.Tags',
        'plugin.PlumSearch.ArticlesTags',
        'plugin.PlumSearch.Authors',
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
    public function setUp(): void
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Articles');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown object "name"');
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
        $result = $this->Articles->find('filters', [])->enableHydration(false)->toArray();
        $this->assertEquals(count($result), 3);

        $filterParameters = [
            'title' => 'First',
            'tag' => 'tag1',
        ];
        $result = $this->Articles->find('filters', $filterParameters)->enableHydration(false)->toArray();
        $expected = [[
            'id' => 1,
            'author_id' => 1,
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 'Y',
        ]];
        $this->assertEquals($expected, $result);

        $filterParameters = [
            'title' => 'First',
            'tag' => 'tag3',
        ];
        $result = $this->Articles->find('filters', $filterParameters)->enableHydration(false)->toArray();
        $expected = [];
        $this->assertEquals($expected, $result);

        $this->Articles->addFilter('author_name', [
            'className' => 'Value',
            'field' => 'Authors.name',
        ]);
        $filterParameters = [
            'author_name' => 'larry',
            'tag' => 'tag3',
        ];
        $result = $this->Articles->find('withAuthors')->find('filters', $filterParameters)->enableHydration(false)->toArray();
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
