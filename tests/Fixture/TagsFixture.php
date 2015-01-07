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
namespace PlumSearch\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Class TagFixture
 *
 */
class TagsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = array(
        'id' => ['type' => 'integer', 'null' => false],
        'name' => ['type' => 'string', 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    );

    /**
     * records property
     *
     * @var array
     */
    public $records = array(
        array('name' => 'tag1'),
        array('name' => 'tag2'),
        array('name' => 'tag3'),
    );
}
