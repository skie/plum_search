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
namespace PlumSearch\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Class TagFixture
 */
class TagsFixture extends TestFixture
{
    /**
     * records property
     *
     * @var array
     */
    public array $records = [
        ['name' => 'tag1'],
        ['name' => 'tag2'],
        ['name' => 'tag3'],
    ];
}
