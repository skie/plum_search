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
namespace PlumSearch\Test\App\Controller;

use PlumSearch\Controller\Component\FilterComponent;
use PlumSearch\Test\App\Model\Table\ArticlesTable;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property FilterComponent $Filter
 */
class ArticlesController extends AppController
{
    //	use AutocompleteTrait;

    public $helpers = [
        'PlumSearch.Search',
    ];

    /**
     * initialize callback
     *
     * @return void
     */
    public function initialize()
    {
        $author = $this->Articles->Authors;
        $this->loadComponent('Paginator');
        $this->loadComponent('PlumSearch.Filter', [
            'formName' => 'Article',
            'parameters' => [
                ['name' => 'title', 'className' => 'Input'],
                [
                    'name' => 'author_id',
                    'className' => 'Select',
                    'finder' => $author->find('list'),
                ],
            ]
        ]);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('articles', $this->Paginator->paginate($this->Filter->prg($this->Articles)));
    }

    /**
     * Index method
     *
     * @return void
     */
    public function search()
    {
        $query = $this->Filter->prg($this->Articles->find('withAuthors'));
        $this->set('articles', $this->Paginator->paginate($query));
    }
}
