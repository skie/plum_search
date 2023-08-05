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

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use PlumSearch\Model\Filter\MultipleFilter;
use PlumSearch\Model\FilterRegistry;

/**
 * PlumSearch\Model\Filter\MultipleFilter Test Case
 */
class MultipleFilterTest extends TestCase
{
    public array $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

    protected \Cake\ORM\Table $Table;

    protected \PlumSearch\Model\FilterRegistry $FilterRegistry;

    protected ?\PlumSearch\Model\Filter\MultipleFilter $MultipleFilter = null;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Table = TableRegistry::getTableLocator()->get('Articles');
        $this->FilterRegistry = new FilterRegistry($this->Table);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Table);
        unset($this->FilterRegistry);
        parent::tearDown();
    }

    /**
     * Data provider for testApply method
     *
     * @return array
     */
    public static function applyDataProvider()
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
            'fields' => ['title', 'body'],
        ];

        if ($type !== false) {
            $options['type'] = $type;
        }

        $this->MultipleFilter = new MultipleFilter($this->FilterRegistry, $options);

        $query = $this->Table->find('all');
        $this->MultipleFilter->apply($query, ['name' => 'test']);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), __('(title LIKE :c0 {0} body LIKE :c1)', $operator));
        $this->assertEquals(Hash::get($binder->bindings(), ':c0.value'), '%test%');
        $this->assertEquals(Hash::get($binder->bindings(), ':c1.value'), '%test%');
    }
}
