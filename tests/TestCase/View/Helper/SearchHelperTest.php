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
namespace PlumSearch\Test\TestCase\View\Helper;

use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Query;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PlumSearch\Test\App\Controller\ArticlesController;
use PlumSearch\Test\App\Controller\ExtArticlesController;
use PlumSearch\View\Helper\SearchHelper;
use Cake\Routing\Router;

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
    public $fixtures = [
        'plugin.plum_search.authors',
        'plugin.plum_search.articles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
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
    public function tearDown()
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
        $request = $request = new Request([
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

        $this->Controller = new ArticlesController($request, new Response());
        $this->Controller->index();
        $parameters = $this->Controller->viewVars['searchParameters'];

        $inputs = $this->Search->inputs($parameters);
        $this->assertEquals(count($inputs), 2);
        $this->assertTrue($inputs['Article.author_id']['options'] instanceof Query);
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
                'empty' => true,
            ],
        ];
        $this->assertEquals($inputs, $expected);
    }

    /**
     * Test input method
     *
     * @return void
     */
    public function testInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = $request = new Request([
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

        $this->Controller = new ArticlesController($request, new Response());
        $this->Controller->index();
        $parameters = $this->Controller->viewVars['searchParameters'];
        $input = $this->Search->input($parameters->get('title'));
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

		Router::scope('/', function ($routes) {
			$routes->connect(
				'/articles/autocomplete',
				['controller' => 'Articles', 'action' => 'autocomplete']
			);
		});
		
        $request = $request = new Request([
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

        $this->Controller = new ExtArticlesController($request, new Response());
        $this->Controller->index();
        $parameters = $this->Controller->viewVars['searchParameters'];
        $input = $this->Search->input($parameters->get('title'));
        $expected = [
            'type' => 'text',
            'required' => false,
            'label' => 'Title',
            'value' => 'Third',
        ];
        $this->assertEquals($input, $expected);

        $inputs = $this->Search->inputs($parameters);
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
            ],
            'Article.author_id' => [
                'type' => 'hidden',
                'required' => false,
                'label' => 'Author',
            ],
        ];
        $this->assertEquals($expected, $inputs);
    }
}
