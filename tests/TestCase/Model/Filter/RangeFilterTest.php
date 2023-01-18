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
use PlumSearch\Model\Filter\RangeFilter;
use PlumSearch\Model\FilterRegistry;

/**
 * PlumSearch\Model\Filter\RangeFilter Test Case
 */
class RangeFilterTest extends TestCase
{
    public array $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

    protected \Cake\ORM\Table $Table;

    protected \PlumSearch\Model\FilterRegistry $FilterRegistry;

    protected \PlumSearch\Model\Filter\RangeFilter $RangeFilter;

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
        $this->RangeFilter = new RangeFilter($this->FilterRegistry, [
            'name' => 'created',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RangeFilter);
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
        $this->RangeFilter = new RangeFilter($this->FilterRegistry, ['field' => 'created']);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApply()
    {
        $query = $this->Table->find('all');
        $date = new \DateTime('2001-01-01');
        $this->RangeFilter->apply($query, ['created' => $date, 'created_to' => null]);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $this->assertEquals($store->sql($query->getValueBinder()), 'Articles.created >= :c0');

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), 'Articles.created >= :c1');
        $this->assertEquals(Hash::get($binder->bindings(), ':c1.value'), $date);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApplyTo()
    {
        $query = $this->Table->find('all');
        $date = new \DateTime('2001-01-01');
        $this->RangeFilter->apply($query, ['created' => null, 'created_to' => $date]);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $this->assertEquals($store->sql($query->getValueBinder()), 'Articles.created <= :c0');

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), 'Articles.created <= :c1');
        $this->assertEquals(Hash::get($binder->bindings(), ':c1.value'), $date);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApplyBoth()
    {
        $query = $this->Table->find('all');
        $date = new \DateTime('2001-01-01');
        $this->RangeFilter->apply($query, ['created' => $date, 'created_to' => $date]);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $this->assertEquals($store->sql($query->getValueBinder()), '(Articles.created >= :c0 AND Articles.created <= :c1)');

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), '(Articles.created >= :c2 AND Articles.created <= :c3)');
        $this->assertEquals(Hash::get($binder->bindings(), ':c2.value'), $date);
        $this->assertEquals(Hash::get($binder->bindings(), ':c3.value'), $date);
    }

    /**
     * Test apply method
     *
     * @return void
     */
    public function testApplyBothWithParam()
    {
        $this->RangeFilter = new RangeFilter($this->FilterRegistry, [
            'name' => 'created',
            'depSuffix' => '_end',
        ]);

        $query = $this->Table->find('all');
        $date = new \DateTime('2001-01-01');
        $this->RangeFilter->apply($query, ['created' => $date, 'created_end' => $date]);
        $store = null;
        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);

        $this->assertEquals($store->sql($query->getValueBinder()), '(Articles.created >= :c0 AND Articles.created <= :c1)');

        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), '(Articles.created >= :c2 AND Articles.created <= :c3)');
        $this->assertEquals(Hash::get($binder->bindings(), ':c2.value'), $date);
        $this->assertEquals(Hash::get($binder->bindings(), ':c3.value'), $date);
    }
}
