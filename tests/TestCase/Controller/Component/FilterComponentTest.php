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
namespace PlumSearch\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PlumSearch\Controller\Component\FilterComponent;
use PlumSearch\FormParameter\InputParameter;
use PlumSearch\FormParameter\ParameterRegistry;
use PlumSearch\Test\App\Controller\ArticlesController;
use RuntimeException;

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
        'plugin.PlumSearch.Articles',
        'plugin.PlumSearch.Tags',
        'plugin.PlumSearch.ArticlesTags',
        'plugin.PlumSearch.Authors',
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
    public function setUp(): void
    {
        parent::setUp();
        $this->Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['redirect'])
            ->getMock();
        $registry = new ComponentRegistry($this->Controller);
        $this->Component = new FilterComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown object "name"');
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
        $this->Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['redirect'])
            ->getMock();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->Controller->setRequest(new ServerRequest([
            'webroot' => '/dir/',
            'params' => [
                'action' => 'index',
                'pass' => [],
            ],
            'post' => [
                'username' => 'admin',
            ],
        ]));
        $this->Controller->setResponse(new Response());
        $registry = new ComponentRegistry($this->Controller);
        $this->Component = new FilterComponent($registry);
        $this->Component->addParam('username', ['className' => 'Input']);

        $redirectExpectation = [
                '?' => [
                    'username' => 'admin',
                ],
                'action' => 'index',
                'username' => 'admin',
        ];
        $this->Controller->expects($this->once())
            ->method('redirect')
            ->with($redirectExpectation)
            ->will($this->returnValue($this->Controller->getResponse()));
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
        $request = new ServerRequest([
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
        $this->Controller = $this->getMockBuilder('PlumSearch\Test\App\Controller\ArticlesController')
            ->setMethods(['redirect'])
            ->setConstructorArgs([$request, $response, 'Articles'])
            ->getMock();

        $redirectExpectation = [
                '?' => [
                    'title' => 'first',
                ],
                'action' => 'index',
                'title' => 'first',
        ];
        $this->Controller->expects($this->once())
            ->method('redirect')
            ->with($redirectExpectation)
            ->will($this->returnValue($this->Controller->getResponse()));
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
        $request = new ServerRequest([
            'webroot' => '/dir/',
            'params' => [
                'action' => 'index',
                'pass' => [],
            ],
            'query' => [
                'title' => 'Third',
            ],
        ]);
        $this->Controller->setRequest($request);
        $this->Controller = new ArticlesController($request, new Response());
        $this->Controller->index();

        $this->assertEquals(count($this->Controller->viewBuilder()->getVar('articles')), 1);
        $article = $this->Controller->viewBuilder()->getVar('articles')->toArray()[0];
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

        $this->Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['redirect'])
            ->getMock();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->Controller->setRequest(new ServerRequest([
            'webroot' => '/dir/',
            'query' => [
                'username' => 'admin',
            ],
        ]));
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
