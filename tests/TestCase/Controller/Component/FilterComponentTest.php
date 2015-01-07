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
namespace PlumSearch\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Controller\Component\FilterComponent;
use PlumSearch\FormParameter\InputParameter;
use PlumSearch\FormParameter\ParameterRegistry;
use PlumSearch\Test\App\Controller\ArticlesController;

/**
 * PlumSearch\Controller\Component\FilterComponent Test Case
 */
class FilterComponentTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.plum_search.authors',
        'plugin.plum_search.articles',
    ];

    /**
     * Controller
     *
     * @var \Cake\Controller\Controller
     */
    public $Controller;

    /**
     * Component
     *
     * @var \PlumSearch\Controller\Component\FilterComponent
     */
    public $Component;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Controller = $this->getMock('Cake\Controller\Controller', ['redirect']);
        $registry = new ComponentRegistry($this->Controller);
        $this->Component = new FilterComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Component);

        parent::tearDown();
    }

    /**
     * Test parameters method
     *
     * @return void
     */
    public function testParameters()
    {
        $input = $this->Component->parameters();
        $this->assertTrue($input instanceof ParameterRegistry);
    }

    /**
     * Test addParam method
     *
     * @return void
     */
    public function testAddParam()
    {
        $this->Component->addParam('name', ['className' => 'Input']);
        $input = $this->Component->parameters()->get('name');
        $this->assertTrue($input instanceof InputParameter);
    }

    /**
     * Test removeParam method
     *
     * @return void
     */
    public function testRemoveParam()
    {
        $this->Component->addParam('name', ['className' => 'Input']);
        $this->Component->removeParam('name');
        $input = $this->Component->parameters()->get('name');
        $this->assertNull($input);
    }

    /**
     * Test prg method
     *
     * @return void
     */
    public function testPrgPost()
    {
        $this->Controller = $this->getMock('Cake\Controller\Controller', ['redirect']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->Controller->request = new Request([
            'webroot' => '/dir/',
            'params' => [
                'action' => 'index',
                'pass' => [],
            ],
            'post' => [
                'username' => 'admin',
            ],
        ]);
        $this->Controller->response = new Response();
        $registry = new ComponentRegistry($this->Controller);
        $this->Component = new FilterComponent($registry);
        $this->Component->addParam('username', ['className' => 'Input']);

        $redirectExpectation = [
                '?' => [
                    'username' => 'admin',
                ],
                'action' => 'index',
        ];
        $this->Controller->expects($this->once())
            ->method('redirect')
            ->with($redirectExpectation)
            ->will($this->returnValue($this->Controller->response));
        $table = TableRegistry::get('Articles');
        $this->Component->prg($table);
    }

    /**
     * Test prg method (post: overloaded form name)
     *
     * @return void
     */
    public function testPrgPostWithOtherFormName()
    {
        unset($this->Controller);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request([
            'webroot' => '/dir/',
            'params' => [
                'action' => 'index',
                'pass' => [],
            ],
            'post' => [
                'Article' => [
                    'title' => 'first',
                ],
            ],
        ]);
        $response = new Response();
        $this->Controller = $this->getMock('PlumSearch\Test\App\Controller\ArticlesController', ['redirect'], [
            $request,
            $response,
            'Articles'
        ]);

        $redirectExpectation = [
                '?' => [
                    'title' => 'first',
                ],
                'action' => 'index',
        ];
        $this->Controller->expects($this->once())
            ->method('redirect')
            ->with($redirectExpectation)
            ->will($this->returnValue($this->Controller->response));
        $this->Controller->Filter->prg($this->Controller->Articles);
    }

    /**
     * Test prg method
     *
     * @return void
     */
    public function testPrgGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->Controller->request = $request = new Request([
            'webroot' => '/dir/',
            'params' => [
                'action' => 'index',
                'pass' => [],
            ],
            'query' => [
                'title' => 'Third',
            ],
        ]);
        $this->Controller = new ArticlesController($request, new Response());
        $this->Controller->index();

        $this->assertEquals(count($this->Controller->viewVars['articles']), 1);
        $article = $this->Controller->viewVars['articles']->toArray()[0];
        $this->assertEquals($article->id, 3);
    }

    /**
     * Test values method
     *
     * @return void
     */
    public function testValues()
    {
        $this->assertEquals($this->Component->values(), []);

        $this->Controller = $this->getMock('Cake\Controller\Controller', ['redirect']);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->Controller->request = new Request([
            'webroot' => '/dir/',
            'query' => [
                'username' => 'admin',
            ],
        ]);
        $registry = new ComponentRegistry($this->Controller);
        $this->Component = new FilterComponent($registry);
        $this->Component->addParam('username', ['className' => 'Input']);

        $this->assertEquals($this->Component->values(), ['username' => 'admin']);
    }

    /**
     * Test controller method
     *
     * @return void
     */
    public function testController()
    {
        $this->assertEquals($this->Component->controller(), $this->Controller);
    }
}
