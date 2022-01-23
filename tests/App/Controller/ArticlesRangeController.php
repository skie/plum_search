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
namespace PlumSearch\Test\App\Controller;

/**
 * Articles Controller
 *
 * @property \PlumSearch\Test\App\Model\Table\ArticlesTable $Articles
 * @property FilterComponent $Filter
 */
class ArticlesRangeController extends AppController
{
    /**
     * initialize callback
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->loadModel('Articles');
        $this->loadComponent('Paginator');
        $this->loadComponent('PlumSearch.Filter', [
            'formName' => 'Article',
            'parameters' => [
                [
                    'name' => 'created',
                    'className' => 'Range',
                    'postRenderCallback' => function ($parameter, $view) {
                        $script = 'var a = 1;';

                        return $view->Html->scriptBlock($script, ['block' => null]);
                    },
                ],
            ],
        ]);
        $this->viewBuilder()->addHelpers([
            'PlumSearch.Search',
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
