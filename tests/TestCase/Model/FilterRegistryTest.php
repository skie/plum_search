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
namespace PlumSearch\Test\TestCase\Model;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Model\Filter\Exception\MissingFilterException;
use PlumSearch\Model\Filter\LikeFilter;
use PlumSearch\Model\Filter\ValueFilter;
use PlumSearch\Model\FilterRegistry;
use RuntimeException;

/**
 * PlumSearch\Model\FilterRegistry Test Case
 */
class FilterRegistryTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array
     */
    public array $fixtures = [
        'plugin.PlumSearch.Articles',
    ];

    protected \Cake\ORM\Table $Table;

    protected \PlumSearch\Model\FilterRegistry $FilterRegistry;

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
        Configure::write('App.namespace', 'TestApp');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Table, $this->FilterRegistry);
        parent::tearDown();
    }

    /**
     * Test load value method
     *
     * @return void
     */
    public function testLoad()
    {
        $type = $this->FilterRegistry->load('id', ['className' => 'Value']);
        $this->assertTrue($type instanceof ValueFilter);
        $type = $this->FilterRegistry->load('name', ['className' => 'Like']);
        $this->assertTrue($type instanceof LikeFilter);
    }

    /**
     * Test load unexists class  method
     *
     * @return void
     */
    public function testLoadWrongClass()
    {
        $this->expectException(MissingFilterException::class);
        $this->FilterRegistry->load('name1', ['className' => 'Value2']);
    }

    /**
     * Test load twice class  method
     *
     * @return void
     */
    public function testLoadTwice()
    {
        $this->FilterRegistry->load('name', ['className' => 'Value']);
        $this->expectException(RuntimeException::class);
        $this->FilterRegistry->load('name', ['className' => 'Like']);
    }
}
