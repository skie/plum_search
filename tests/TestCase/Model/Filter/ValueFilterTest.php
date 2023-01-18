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
use PlumSearch\Model\Filter\Exception\MissingFilterException;
use PlumSearch\Model\Filter\ValueFilter;
use PlumSearch\Model\FilterRegistry;

/**
 * PlumSearch\Model\Filter\ValueFilter Test Case
 */
class ValueFilterTest extends TestCase
{
    public array $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

    protected \Cake\ORM\Table $Table;

    protected \PlumSearch\Model\FilterRegistry $FilterRegistry;

    protected \PlumSearch\Model\Filter\ValueFilter $ValueFilter;

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
        $this->ValueFilter = new ValueFilter($this->FilterRegistry, [
            'name' => 'id',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ValueFilter);
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
        $this->ValueFilter = new ValueFilter($this->FilterRegistry, ['field' => 'id']);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApply()
    {
        $query = $this->Table->find('all');
        $this->ValueFilter->apply($query, ['id' => 1]);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $this->assertEquals($store->sql($query->getValueBinder()), 'Articles.id = :c0');

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), 'Articles.id = :c1');
        $this->assertEquals(Hash::get($binder->bindings(), ':c1.value'), 1);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApplyWithFieldDefined()
    {
        $query = $this->Table->find('withAuthors');

        $this->FilterRegistry = new FilterRegistry($this->Table);
        $this->ValueFilter = new ValueFilter($this->FilterRegistry, [
            'name' => 'author_name',
            'field' => 'Authors.name',
        ]);

        $this->ValueFilter->apply($query, [
            'author_name' => 'larry',
        ]);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), 'Authors.name = :c0');
        $this->assertEquals(Hash::get($binder->bindings(), ':c0.value'), 'larry');
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApplyArray()
    {
        $query = $this->Table->find('all');
        $this->ValueFilter->apply($query, ['id' => [1, 2]]);
        $store = null;

        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);
        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), 'Articles.id IN (:c0,:c1)');
        $this->assertEquals(Hash::get($binder->bindings(), ':c0.value'), '1');
        $this->assertEquals(Hash::get($binder->bindings(), ':c1.value'), '2');
    }
}
