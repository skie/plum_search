<?php
declare(strict_types=1);

/**
 * PlumSearch plugin for CakePHP Rapid Development Framework
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Evgeny Tomenko
 * @since         PlumSearch 5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace PlumSearch\Test\TestCase\FormParameter;

use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use PlumSearch\FormParameter\HiddenParameter;
use PlumSearch\FormParameter\LookupParameter;
use PlumSearch\FormParameter\ParameterRegistry;

/**
 * Class LookupParameterTest
 * PlumSearch\FormParameter\LookupParameter Test Case
 *
 * @package PlumSearch\Test\TestCase\FormParameter
 */
class LookupParameterTest extends TestCase
{
    protected ParameterRegistry $ParameterRegistry;

    protected LookupParameter $LookupParam;

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
                'project_id' => 3,
                'project_id_lookup' => 'Test Project',
            ],
        ]);
        $controller = $this->getMockBuilder(\Cake\Controller\Controller::class)
            ->onlyMethods(['redirect'])
            ->setConstructorArgs([$request])
            ->getMock();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->ParameterRegistry = new ParameterRegistry($controller);
        $this->LookupParam = new LookupParameter($this->ParameterRegistry, [
            'name' => 'project_id',
            'autocompleteUrl' => '/api/projects/lookup',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->LookupParam);
        unset($this->ParameterRegistry);
        parent::tearDown();
    }

    /**
     * Test formInputConfig method
     *
     * @return void
     */
    public function testFormInputConfig(): void
    {
        $config = $this->LookupParam->formInputConfig();
        $this->assertNotEmpty($config);
        $this->assertEquals('lookup-autocomplete', $config['class']);
        $this->assertEquals('/api/projects/lookup', $config['data-url']);
        $this->assertEquals('id', $config['data-id-name']);
        $this->assertEquals('name', $config['data-value-name']);
    }

    /**
     * Test viewValues method
     *
     * @return void
     */
    public function testViewValues(): void
    {
        $values = $this->LookupParam->viewValues();
        $this->assertEquals(array_keys($values), ['project_id_lookup', 'project_id']);
        $this->assertEquals($values['project_id_lookup'], $this->LookupParam);
        $this->assertTrue($values['project_id'] instanceof HiddenParameter);
    }

    /**
     * Test visible method
     *
     * @return void
     */
    public function testVisible(): void
    {
        $values = $this->LookupParam->viewValues();
        $this->assertTrue($values['project_id_lookup']->visible());
        $this->assertFalse($values['project_id']->visible());
    }

    /**
     * Test values method
     *
     * @return void
     */
    public function testValues(): void
    {
        $this->assertEquals($this->LookupParam->values(), [
            'project_id_lookup' => 'Test Project',
            'project_id' => 3,
        ]);
    }

    /**
     * Test value method
     *
     * @return void
     */
    public function testValue(): void
    {
        $this->assertEquals($this->LookupParam->value(), 'Test Project');
    }

    /**
     * Test HasOptions method
     *
     * @return void
     */
    public function testHasOptions(): void
    {
        $this->assertFalse($this->LookupParam->hasOptions());
    }

    /**
     * Test parent field functionality
     *
     * @return void
     */
    public function testParentField(): void
    {
        $request = new ServerRequest([
            'webroot' => '/dir/',
            'query' => [
                'test_iteration_id' => 5,
                'test_iteration_id_lookup' => 'Iteration 1',
                'project_id' => 3,
            ],
        ]);
        $controller = $this->getMockBuilder(\Cake\Controller\Controller::class)
            ->onlyMethods(['redirect'])
            ->setConstructorArgs([$request])
            ->getMock();
        $registry = new ParameterRegistry($controller);
        $param = new LookupParameter($registry, [
            'name' => 'test_iteration_id',
            'autocompleteUrl' => '/api/iterations/lookup',
            'parentField' => 'project_id',
            'parentIdParam' => 'project_id',
        ]);

        $config = $param->formInputConfig();
        $this->assertEquals('project_id', $config['data-parent-field']);
        $this->assertEquals('project_id', $config['data-parent-id-param']);
    }

    /**
     * Test dependent fields functionality
     *
     * @return void
     */
    public function testDependentFields(): void
    {
        $param = new LookupParameter($this->ParameterRegistry, [
            'name' => 'project_id',
            'autocompleteUrl' => '/api/projects/lookup',
            'dependentFields' => ['test_iteration_id', 'user_id'],
        ]);

        $config = $param->formInputConfig();
        $this->assertEquals('["test_iteration_id","user_id"]', $config['data-dependent-fields']);
    }

    /**
     * Test additional parents functionality
     *
     * @return void
     */
    public function testAdditionalParents(): void
    {
        $param = new LookupParameter($this->ParameterRegistry, [
            'name' => 'test_iteration_id',
            'autocompleteUrl' => '/api/iterations/lookup',
            'additionalParents' => ['user' => 'user_id', 'scope' => 'scope_id'],
        ]);

        $config = $param->formInputConfig();
        $this->assertEquals('{"user":"user_id","scope":"scope_id"}', $config['data-additional-parents']);
    }
}
