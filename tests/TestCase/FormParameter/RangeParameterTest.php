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
use PlumSearch\FormParameter\ParameterRegistry;
use PlumSearch\FormParameter\RangeParameter;

/**
 * Class RangeParamTest
 * PlumSearch\FormParameter\RangeParam Test Case
 *
 * @package PlumSearch\Test\TestCase\FormParameter
 */
class RangeParameterTest extends TestCase
{
    protected \PlumSearch\FormParameter\ParameterRegistry $ParameterRegistry;

    protected \PlumSearch\FormParameter\RangeParameter $RangeParam;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $request = new ServerRequest([
            'webroot' => '/dir/',
            'query' => [
                'created' => '2001-01-01',
            ],
        ]);
        $controller = $this->getMockBuilder(\Cake\Controller\Controller::class)
            ->onlyMethods(['redirect'])
            ->setConstructorArgs([$request])
            ->getMock();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->ParameterRegistry = new ParameterRegistry($controller);
        $this->RangeParam = new RangeParameter($this->ParameterRegistry, [
            'name' => 'created',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->RangeParam);
        unset($this->ParameterRegistry);
        parent::tearDown();
    }

    /**
     * Test formInputConfig method
     *
     * @return void
     */
    public function testFormInputConfig()
    {
        $this->assertEquals($this->RangeParam->formInputConfig(), ['label' => 'Created From']);
    }

    /**
     * Test viewValues method
     *
     * @return void
     */
    public function testViewValues()
    {
        $values = $this->RangeParam->viewValues();
        $this->assertEquals(array_keys($values), ['created', 'created_to']);
    }

    /**
     * Test visible method
     *
     * @return void
     */
    public function testVisible()
    {
        $values = $this->RangeParam->viewValues();
        $this->assertTrue($values['created_to']->visible());
        $this->assertTrue($values['created']->visible());
    }

    /**
     * Test values method
     *
     * @return void
     */
    public function testValues()
    {
        $this->assertEquals($this->RangeParam->values(), [
            'created' => '2001-01-01',
            'created_to' => null,
        ]);
    }

    /**
     * Test value method
     *
     * @return void
     */
    public function testValue()
    {
        $this->assertEquals($this->RangeParam->value(), '2001-01-01');
    }

    /**
     * Test HasOptions method
     *
     * @return void
     */
    public function testHasOptions()
    {
        $this->assertFalse($this->RangeParam->hasOptions());
    }
}
