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
use PlumSearch\Model\Filter\LikeFilter;
use PlumSearch\Model\FilterRegistry;

/**
 * PlumSearch\Model\Filter\LikeFilter Test Case
 */
class LikeFilterTest extends TestCase
{
    public $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

    /**
     * @var \Cake\ORM\Table
     */
    protected $Table;

    /**
     * @var FilterRegistry
     */
    protected $FilterRegistry;

    /**
     * @var \PlumSearch\Model\Filter\AbstractFilter
     */
    protected $LikeFilter;

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
        $this->LikeFilter = new LikeFilter($this->FilterRegistry, [
            'name' => 'name',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
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

        $query->traverseParts(function ($d, $type) use (&$store) {
            $store = $d;
        }, ['where']);
        $binder = $query->getValueBinder();
        $this->assertEquals($store->sql($binder), 'Articles.name LIKE :c0');
        $this->assertEquals(Hash::get($binder->bindings(), ':c0.value'), '%test%');
    }
}
