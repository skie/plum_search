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
use Cake\TestSuite\TestCase;
use PlumSearch\FormParameter\AutocompleteParameter;
use PlumSearch\FormParameter\Exception\MissingParameterException;
use PlumSearch\FormParameter\HiddenParameter;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class AutocompleteParamTest
 * PlumSearch\FormParameter\AutocompleteParam Test Case
 *
 * @package PlumSearch\Test\TestCase\FormParameter
 */
class AutocompleteParameterTest extends TestCase
{
    /**
     * @var ParameterRegistry
     */
    protected $ParameterRegistry;

    /**
     * @var \PlumSearch\FormParameter\AutocompleteParameter
     */
    protected $AutocompleteParam;

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
            'query' => [
                'item_id' => 7,
                'item_id_lookup' => 'cool item',
            ],
        ]));
        $this->ParameterRegistry = new ParameterRegistry($controller);
        $this->AutocompleteParam = new AutocompleteParameter($this->ParameterRegistry, [
            'name' => 'item_id',
            'autocompleteAction' => function () {
                return [];
            },
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->AutocompleteParam);
        unset($this->ParameterRegistry);
        parent::tearDown();
    }

    /**
     * Test constructor method
     *
     * @return void
     */
    public function testConstruct()
    {
        $this->expectException(MissingParameterException::class);
        $this->AutocompleteParam = new AutocompleteParameter($this->ParameterRegistry, ['name' => 'item_id']);
    }

    /**
     * Test formInputConfig method
     *
     * @return void
     */
    public function testFormInputConfig()
    {
        $this->assertEquals($this->AutocompleteParam->formInputConfig(), []);
    }

    /**
     * Test viewValues method
     *
     * @return void
     */
    public function testViewValues()
    {
        $values = $this->AutocompleteParam->viewValues();
        $this->assertEquals(array_keys($values), ['item_id_lookup', 'item_id']);
        $this->assertEquals($values['item_id_lookup'], $this->AutocompleteParam);
        $this->assertTrue($values['item_id'] instanceof HiddenParameter);
    }

    /**
     * Test visible method
     *
     * @return void
     */
    public function testVisible()
    {
        $values = $this->AutocompleteParam->viewValues();
        $this->assertTrue($values['item_id_lookup']->visible());
        $this->assertFalse($values['item_id']->visible());
    }

    /**
     * Test values method
     *
     * @return void
     */
    public function testValues()
    {
        $this->assertEquals($this->AutocompleteParam->values(), [
            'item_id_lookup' => 'cool item',
            'item_id' => 7,
        ]);
    }

    /**
     * Test value method
     *
     * @return void
     */
    public function testValue()
    {
        $this->assertEquals($this->AutocompleteParam->value(), 'cool item');
    }

    /**
     * Test HasOptions method
     *
     * @return void
     */
    public function testHasOptions()
    {
        $this->assertFalse($this->AutocompleteParam->hasOptions());
    }
}
