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

use Cake\TestSuite\TestCase;
use PlumSearch\FormParameter\Exception\MissingParameterException;
use PlumSearch\FormParameter\InputParameter;
use PlumSearch\FormParameter\ParameterRegistry;
use RuntimeException;

/**
 * Class ParameterRegistryTest
 * PlumSearch\FormParameter\ParameterRegistry Test Case
 *
 * @package PlumSearch\Test\TestCase\FormParameter
 */
class ParameterRegistryTest extends TestCase
{
    protected \PlumSearch\FormParameter\ParameterRegistry $ParameterRegistry;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $controller = $this->getMockBuilder(\Cake\Controller\Controller::class)
            ->setMethods(['redirect'])
            ->getMock();
        $this->ParameterRegistry = new ParameterRegistry($controller);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ParameterRegistry);
        parent::tearDown();
    }

    /**
     * Test load value method
     *
     * @return void
     */
    public function testLoad()
    {
        $type = $this->ParameterRegistry->load('id', ['className' => 'Input']);
        $this->assertTrue($type instanceof InputParameter);
        $type = $this->ParameterRegistry->load('name', ['className' => 'Input']);
        $this->assertTrue($type instanceof InputParameter);
    }

    /**
     * Test load unexists class  method
     *
     * @return void
     */
    public function testLoadWrongClass()
    {
        $this->expectException(MissingParameterException::class);
        $this->ParameterRegistry->load('name1', ['className' => 'Input2']);
    }

    /**
     * Test load twice class  method
     *
     * @return void
     */
    public function testLoadTwice()
    {
        $this->ParameterRegistry->load('name', ['className' => 'Input']);
        $this->expectException(RuntimeException::class);
        $this->ParameterRegistry->load('name', ['className' => 'Select']);
    }
}
