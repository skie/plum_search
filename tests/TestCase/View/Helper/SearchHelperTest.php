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
namespace PlumSearch\Test\TestCase\View\Helper;

use Cake\Http\ServerRequest;
use Cake\ORM\Query\SelectQuery;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PlumSearch\Test\App\Controller\ArticlesController;
use PlumSearch\Test\App\Controller\ArticlesRangeController;
use PlumSearch\Test\App\Controller\ExtArticlesController;
use PlumSearch\View\Helper\SearchHelper;

/**
 * PlumSearch\View\Helper\SearchHelper Test Case
 */
class SearchHelperTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array
     */
    public array $fixtures = [
        'plugin.PlumSearch.Articles',
        'plugin.PlumSearch.Tags',
        'plugin.PlumSearch.ArticlesTags',
        'plugin.PlumSearch.Authors',
    ];

    /**
     * @var \Cake\Controller\Controller
     */
    protected $Controller;

    protected \Cake\View\View $View;

    protected \PlumSearch\View\Helper\SearchHelper $Search;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->View = new View();
        $this->Search = new SearchHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Search);

        parent::tearDown();
    }

    /**
     * Test inputs method
     *
     * @return void
     */
    public function testInputs()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new ServerRequest([
            'webroot' => '/articles/',
            'params' => [
                'controller' => 'Articles',
                'action' => 'index',
                'pass' => [],
            ],
            'query' => [
                'title' => 'Third',
            ],
        ]);

        $this->Controller = new ArticlesController($request);
        $this->Controller->index();
        $parameters = $this->Controller->viewBuilder()->getVar('searchParameters');

        $inputs = $this->Search->controls($parameters);
        $this->assertEquals(count($inputs), 2);
        $this->assertTrue($inputs['Article.author_id']['options'] instanceof SelectQuery);
        $inputs['Article.author_id']['options'] = $inputs['Article.author_id']['options']->toArray();
        $expected = [
            'Article.title' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Title',
                'value' => 'Third',
            ],
            'Article.author_id' => [
                'type' => 'select',
                'options' => [
                    1 => 'evgeny',
                    2 => 'mark',
                    3 => 'larry',
                ],
                'required' => false,
                'label' => 'Author',
                'value' => '',
                'empty' => true,
            ],
        ];
        $this->assertEquals($inputs, $expected);
    }

    /**
     * Test post render method
     *
     * @return void
     */
    public function testPostRender()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new ServerRequest([
            'webroot' => '/articles/',
            'params' => [
                'controller' => 'Articles',
                'action' => 'index',
                'pass' => [],
            ],
            'query' => [
                'title' => 'Third',
            ],
        ]);

        $this->Controller = new ArticlesRangeController($request);
        $this->Controller->index();
        $parameters = $this->Controller->viewBuilder()->getVar('searchParameters');

        $inputs = $this->Search->controls($parameters);
        $expected = [
            'Article.created' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Created From',
                'value' => '',
            ],
            'Article.created_to' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Created To',
                'value' => '',
            ],
        ];
        $this->assertEquals($inputs, $expected);

        $script = $this->Search->postRender($parameters);
        $expectedScript = '<script>var a = 1;</script>';
        $this->assertEquals($script, $expectedScript);
    }

    /**
     * Test input method
     *
     * @return void
     */
    public function testInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new ServerRequest([
            'webroot' => '/articles/',
            'params' => [
                'controller' => 'Articles',
                'action' => 'index',
                'pass' => [],
            ],
            'query' => [
                'title' => 'Third',
            ],
        ]);

        $this->Controller = new ArticlesController($request);
        $this->Controller->index();
        $parameters = $this->Controller->viewBuilder()->getVar('searchParameters');
        $input = $this->Search->control($parameters->get('title'));
        $expected = [
            'type' => 'text',
            'required' => false,
            'label' => 'Title',
            'value' => 'Third',
        ];
        $this->assertEquals($input, $expected);
    }

    /**
     * Test input method
     *
     * @return void
     */
    public function testInputsExt()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        Router::reload();
        $builder = Router::createRouteBuilder('/');
        $builder->scope('/', function ($routes) {
            $routes->connect(
                '/articles/autocomplete',
                ['controller' => 'Articles', 'action' => 'autocomplete']
            );
        });

        $request = new ServerRequest([
            'webroot' => '/articles/',
            'params' => [
                'controller' => 'Articles',
                'action' => 'index',
                'pass' => [],
            ],
            'query' => [
                'title' => 'Third',
            ],
        ]);

        $this->Controller = new ExtArticlesController($request);
        $this->Controller->index();
        $parameters = $this->Controller->viewBuilder()->getVar('searchParameters');
        $input = $this->Search->control($parameters->get('title'));
        $expected = [
            'type' => 'text',
            'required' => false,
            'label' => 'Title',
            'value' => 'Third',
        ];
        $this->assertEquals($input, $expected);

        $inputs = $this->Search->controls($parameters);
        $expected = [
            'Article.title' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Title',
                'value' => 'Third',
            ],
            'Article.author_id_lookup' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Author',
                'data-url' => '/articles/autocomplete',
                'class' => 'autocomplete',
                'data-name' => 'author_id',
                'value' => '',
            ],
            'Article.author_id' => [
                'type' => 'hidden',
                'required' => false,
                'label' => 'Author',
                'value' => '',
            ],
        ];
        $this->assertEquals($expected, $inputs);
    }
}
