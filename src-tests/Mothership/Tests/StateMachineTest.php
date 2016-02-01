<?php

/**
 * Mothership GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to office@mothership.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.mothership.de for more information.
 *
 * @category  Mothership
 * @package   Mothership_state_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright Copyright (c) 2015 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */

use \Mothership\Examples\Simple\SimpleStateMachine;
use \Mothership\Examples\IfConditions\IfConditionsStateMachine;
use \Mothership\Examples\BooleanConditions\BooleanConditionsStateMachine;
/**
 * Class StateMachineTest
 *
 * @category  Mothership
 * @package   Mothership_State_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2015 Mothership GmbH
 * @link      http://www.mothership.de/
 */
class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    protected $state_machine_dir;
    protected $yamlfile = [];
    protected $statemachine_test;

    /**
     * @dataProvider stateMachineProvider
     */
    public function testCreation($dir, $class, $yml)
    {
        $state_machine = new $class($yml);
        $this->isInstanceOf($class, $state_machine);
        $this->assertTrue($state_machine->run());
    }

    /**
     * @dataProvider stateMachineProvider
     */
    public function testRenderGraph($dir, $class, $yml)
    {
        $path = $dir . 'workflow.png';
        $state_machine = new $class($yml);
        $state_machine->renderGraph($path, false);
        $this->assertTrue(file_exists($path));
    }


    public function stateMachineProvider()
    {
        $this->state_machine_dir = $this->getExemplesDir();
        $state_machines = [];
        foreach ($this->state_machine_dir as $dir) {
            array_push($state_machines, [
                $dir['PATH'],
                "Mothership\\Examples\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "StateMachine",
                getcwd() . '/src-tests/Mothership/Examples/' . $dir['NAME'] . '/Workflow.yml',
            ]);
            array_push($this->yamlfile, getcwd() . '/src-tests/Mothership/Examples/' . $dir['NAME'] . '/Workflow.yml');
        }
        return $state_machines;
    }

    /**
     * @dataProvider stateMachineProvider
     */
    public function testParseYAML($dir, $class, $yml)
    {
        $state_machine = new $class($yml);
        $yaml_array = $this->invokeMethod($state_machine, 'parseYAML');
        $this->assertArrayHasKey('states', $yaml_array);
        $this->assertArrayHasKey('class', $yaml_array);
        $this->assertArrayHasKey('args', $yaml_array['class']);
        foreach ($yaml_array['states'] as $state) {
            $this->assertArrayHasKey('name', $state);
            $this->assertArrayHasKey('type', $state);
            if ($state['type'] != 'initial') {
                $this->assertArrayHasKey('transitions_from', $state);
                $this->assertArrayHasKey('transitions_to', $state);
            }
        }
    }

    /**
     * @expectedException     Mothership\StateMachine\Exception\StateMachineException
     */
    public function testMethodNotImplementedException() {
        $state_machine_class = "Mothership\\Examples\\Fail\\FailStateMachine";
        $state_machine = new $state_machine_class(getcwd() . '/src-tests/Mothership/Examples/Fail/Workflow.yml');
    }

    /**
     * @dataProvider stateMachineProvider
     */
    public function testInitWorkflow($dir, $class, $yml)
    {
        $state_machine = new $class($yml, new \Symfony\Component\Console\Output\ConsoleOutput());
        $yaml_array = $this->invokeMethod($state_machine, 'parseYAML');
        $this->invokeMethod($state_machine, "initWorkflow");
        $this->assertEquals($yaml_array['class']['name'], $this->getPropertyClass($state_machine,
            "workflow"));
    }

}

