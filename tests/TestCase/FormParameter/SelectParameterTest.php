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
namespace PlumSearch\Test\TestCase\FormParameter;

use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\FormParameter\ParameterRegistry;
use PlumSearch\FormParameter\SelectParameter;

/**
 * Class SelectParameterTest
 * PlumSearch\FormParameter\SelectParameter Test Case
 *
 * @package PlumSearch\Test\TestCase\FormParameter
 */
class SelectParameterTest extends TestCase
{
    /**
     * Fixtures
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
     * Parameter
     *
     * @var \PlumSearch\FormParameter\SelectParameter
     */
    public $SelectParam;

    /**
     * Parameter Registry
     *
     * @var \PlumSearch\FormParameter\ParameterRegistry
     */
    public $ParameterRegistry;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['redirect'])
            ->getMock();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $controller->setRequest(new ServerRequest([
            'webroot' => '/dir/',
            'query' => ['username' => 'admin'],
        ]));
        $articles = TableRegistry::get('Articles');
        $this->ParameterRegistry = new ParameterRegistry($controller);
        $this->SelectParam = new SelectParameter($this->ParameterRegistry, [
            'name' => 'username',
            'finder' => $articles->find('list'),
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SelectParam);
        parent::tearDown();
    }

    /**
     * Test __construct method
     *
     * @expectedException \PlumSearch\FormParameter\Exception\MissingParameterException
     * @return void
     */
    public function testConstruct()
    {
        $this->SelectParam = new SelectParameter($this->ParameterRegistry, [
            'name' => 'username',
        ]);
    }

    /**
     * Test visible method
     *
     * @return void
     */
    public function testVisible()
    {
        $this->assertTrue($this->SelectParam->visible());
    }

    /**
     * Test formInputConfig method
     *
     * @return void
     */
    public function testFormInputConfig()
    {
        $formParams = $this->SelectParam->formInputConfig();
        $this->assertEquals($formParams['type'], 'select');
    }

    /**
     * Test viewValues method
     *
     * @return void
     */
    public function testViewValues()
    {
        $this->assertEquals($this->SelectParam->viewValues(), ['username' => $this->SelectParam]);
    }

    /**
     * Test values method
     *
     * @return void
     */
    public function testValues()
    {
        $this->assertEquals($this->SelectParam->values(), ['username' => 'admin']);
    }

    /**
     * Test value method
     *
     * @return void
     */
    public function testValue()
    {
        $this->assertEquals($this->SelectParam->value(), 'admin');
    }

    /**
     * Test HasOptions method
     *
     * @return void
     */
    public function testHasOptions()
    {
        $this->assertTrue($this->SelectParam->hasOptions());
    }
}
