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
use PlumSearch\FormParameter\InputParameter;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class InputParameterTest
 * PlumSearch\FormParameter\InputParameter Test Case
 *
 * @package PlumSearch\Test\TestCase\FormParameter
 */
class InputParameterTest extends TestCase
{
    protected \PlumSearch\FormParameter\ParameterRegistry $ParameterRegistry;

    protected \PlumSearch\FormParameter\InputParameter $InputParam;

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
            'query' => ['username' => 'admin'],
        ]);
        $controller = $this->getMockBuilder(\Cake\Controller\Controller::class)
            ->setMethods(['redirect'])
            ->setConstructorArgs([$request])
            ->getMock();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->ParameterRegistry = new ParameterRegistry($controller);
        $this->InputParam = new InputParameter($this->ParameterRegistry, ['name' => 'username']);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->InputParam);
        parent::tearDown();
    }

    /**
     * Test visible method
     *
     * @return void
     */
    public function testVisible()
    {
        $this->assertTrue($this->InputParam->visible());
    }

    /**
     * Test formInputConfig method
     *
     * @return void
     */
    public function testFormInputConfig()
    {
        $this->assertEquals($this->InputParam->formInputConfig(), []);
    }

    /**
     * Test viewValues method
     *
     * @return void
     */
    public function testViewValues()
    {
        $this->assertEquals($this->InputParam->viewValues(), ['username' => $this->InputParam]);
    }

    /**
     * Test values method
     *
     * @return void
     */
    public function testValues()
    {
        $this->assertEquals($this->InputParam->values(), ['username' => 'admin']);
    }

    /**
     * Test value method
     *
     * @return void
     */
    public function testValue()
    {
        $this->assertEquals($this->InputParam->value(), 'admin');
    }

    /**
     * Test HasOptions method
     *
     * @return void
     */
    public function testHasOptions()
    {
        $this->assertFalse($this->InputParam->hasOptions());
    }
}
