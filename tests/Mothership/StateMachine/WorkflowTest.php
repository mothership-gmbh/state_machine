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
use Symfony\Component\Yaml\Yaml;

use Exemple\Simple\SimpleWorkflow;
use Exemple\IfConditions\IfConditionsWorkflow;
use Exemple\Fail\FailStateMachine;
use Exemple\Fail\FailWorkflow;
/**
 * WorkflowTest
 *
 * @category  Mothership
 * @package   Mothership_State_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2015 Mothership GmbH
 * @link      http://www.mothership.de/
 */
class WorkflowTest extends StateMachineTestCase
{
    /**
     * @dataProvider    workflowGoodProvider
     */
    public function testGoodWorkflow($worklowClass, $arguments)
    {
        $workflow = new $worklowClass($arguments);
        $this->isInstanceOf($worklowClass, $workflow);
        /**
         * get CurrentStatus
         */
        $status = $workflow->getCurrentStatus();
        $this->isInstanceOf('Mothership\StateMachine\StatusInterface', $status);
        /**
         * getStatus
         */
        $status = $workflow->getStatus('second_state');
        $this->isInstanceOf('Mothership\StateMachine\StatusInterface', $status);
        /**
         * run
         */
        $this->assertTrue($workflow->run());
    }


    /**
     * @dataProvider workflowFailProvider
     * @expectedException     Mothership\StateMachine\Exception\WorkflowException
     */
    public function testExceptionInConstructor($worklowClass, $arguments)
    {
        $workflow = new $worklowClass($arguments);
    }

    /**
     * Good provider for instantiate a workflow
     * @return array
     */
    public function workflowGoodProvider()
    {
        $this->state_machine_dir = $this->getExemplesDir();
        $workflow = [];
        foreach ($this->state_machine_dir as $dir) {
            $state_machine_class = "Exemple\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "StateMachine";
            $state_machine = new $state_machine_class($dir['PATH'] . 'Workflow.yml');
            array_push($workflow, [
                "Exemple\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "Workflow",
                $this->invokeMethod($state_machine, "parseYAML"),
            ]);
        }
        return $workflow;
    }

    /**
     * Bad provider for instantiate a workflow
     * @return array
     */
    public function workflowFailProvider()
    {
        $workflow = [];
        array_push($workflow, [
            "Exemple\\Simple\\SimpleWorkflow",
            []
        ]);
        array_push($workflow, [
            "Exemple\\Simple\\SimpleWorkflow",
            ['arg1' => 1, 'args2' => 2]
        ]);


        return $workflow;
    }

    /**
     * @expectedException     Mothership\StateMachine\Exception\WorkflowException
     */
    public function testMethodNotImplementedException()
    {
        $workflow_class = "Exemple\\Fail\\FailWorkflow";
        $workflow = new $workflow_class([
            'states' => [
                'start' => [],
                'second_state' => [],
                'third' => [],
                'final' => []
            ]
        ]);
    }

    /**
     * @dataProvider    workflowGoodProvider
     */
    public function testVars($worklowClass, $arguments)
    {
        $workflow = new $worklowClass($arguments);
        $vars = $this->getPropertyValue($workflow, "vars");
        $this->assertArrayHasKey('states', $vars);
        $this->assertArrayHasKey('class', $vars);
        $this->assertArrayHasKey('args', $vars['class']);
    }
}