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
namespace PlumSearch\Test\App\Model\Table;

use Cake\ORM\Query;

/**
 * Articles Table
 *
 * @property \PlumSearch\Test\App\Model\Table\AuthorsTable|\Cake\ORM\Association\BelongsTo $Authors
 * @method \PlumSearch\Model\FilterRegistry filters()
 * @method \Cake\ORM\Table addFilter(string $name, array $options = [])
 * @method \Cake\ORM\Table removeFilter(string $name)
 */
class ArticlesTable extends \Cake\ORM\Table
{
    /**
     * Initialize method
     *
     * @param  array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('articles');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('PlumSearch.Filterable');
        $this->addFilter('title', ['className' => 'Like']);
        $this->addFilter('tag', ['className' => 'Tags']);
        // $this->addFilter('language', ['className' => 'Value']);
        $this->addFilter('author_id', ['className' => 'Value']);

        $this->belongsTo('Authors', [
            'foreignKey' => 'author_id',
        ]);
    }

    /**
     * Authors search finder
     *
     * @param  Query $query query object instance
     * @return $this
     */
    public function findWithAuthors(Query $query)
    {
        return $query->matching('Authors');
    }
}
