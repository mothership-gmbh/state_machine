<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine\Tests;

use Symfony\Component\Yaml\Yaml;

use Mothership\StateMachine\Examples\Simple\SimpleWorkflow;
use Mothership\StateMachine\Examples\IfConditions\IfConditionsWorkflow;
use Mothership\StateMachine\Examples\Fail\FailStateMachine;
use Mothership\StateMachine\Examples\Fail\FailWorkflow;

/**
 * WorkflowTest
 *
 * @category  Mothership
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 * @link      http://www.mothership.de/
 */
class WorkflowTest extends \Mothership\StateMachine\Tests\StateMachineTestCase
{
    //public function stateMachineHaveFinalState

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
        $this->state_machine_dir = $this->getExamplesDir();
        $workflow = [];
        foreach ($this->state_machine_dir as $dir) {
            $state_machine_class = "Mothership\\StateMachine\\Examples\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "StateMachine";
            $state_machine = new $state_machine_class($dir['PATH'] . 'Workflow.yml');
            array_push($workflow, [
                "Mothership\\StateMachine\\Examples\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "Workflow",
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
            "Mothership\\StateMachine\\Examples\\Simple\\SimpleWorkflow",
            []
        ]);
        array_push($workflow, [
            "Mothership\\StateMachine\\Examples\\Simple\\SimpleWorkflow",
            ['arg1' => 1, 'args2' => 2]
        ]);


        return $workflow;
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Workflow
     * @group Mothership_StateMachine_Workflow_2
     *
     * @dataProvider workflowGoodProvider
     */
    public function testVars($worklowClass, $arguments)
    {
        $workflow = new $worklowClass($arguments);
        $vars = $this->getPropertyValue($workflow, 'vars');
        $this->assertArrayHasKey('states', $vars);
        $this->assertArrayHasKey('class', $vars);
        $this->assertArrayHasKey('args', $vars['class']);
    }
}