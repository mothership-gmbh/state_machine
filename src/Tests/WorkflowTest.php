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
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright Copyright (c) 2016 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
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
    /**
     * @group bosco
     * @test
     *
     * @throws \Mothership\StateMachine\Exception\StateMachineException
     */
    public function acceptance()
    {
        $workflow = new \Mothership\StateMachine\Examples\SimpleLoop\SimpleLoopStateMachine(
            '.' . $this->exampleDir . '/SimpleLoop/Workflow.yml'
        );
        $t = $workflow->run();

        $wrongStates = [
            [
                'name' => 'start'
            ],
            [
                'name' => 'prepare_collection'
            ],
            [
                'name' => 'process_items'
            ],
            [
                'name' => 'start'
            ]
        ];

        $workingStates = [
            [
                'name' => 'start'
            ],
            [
                'name' => 'prepare_collection'
            ],
            [
                'name' => 'process_items'
            ],
            [
                'name' => 'has_more',
                'return' => 1
            ],
            [
                'name' => 'process_items'
            ],
            [
                'name' => 'has_more',
                'return' => 0
            ],
            [
                'name' => 'do_it_again',
                'return' => 0
            ],
            [
                'name' => 'finish',
            ],

        ];

        $test = array (
            0 =>
                array (
                    'name' => 'start',
                    'return' => 'omh',
                ),
            1 =>
                array (
                    'name' => 'prepare_collection',
                ),
            2 =>
                array (
                    'name' => 'process_items',
                ),
            3 =>
                array (
                    'name' => 'has_more',
                    'return' => true,
                ),
            4 =>
                array (
                    'name' => 'process_items',
                ),
            5 =>
                array (
                    'name' => 'has_more',
                    'return' => true,
                ),
            6 =>
                array (
                    'name' => 'process_items',
                ),
            7 =>
                array (
                    'name' => 'has_more',
                    'return' => true,
                ),
            8 =>
                array (
                    'name' => 'process_items',
                ),
            9 =>
                array (
                    'name' => 'has_more',
                    'return' => true,
                ),
            10 =>
                array (
                    'name' => 'process_items',
                ),
            11 =>
                array (
                    'name' => 'has_more',
                    'return' => false,
                ),
            12 =>
                array (
                    'name' => 'do_it_again',
                    'return' => false,
                ),
            13 =>
                array (
                    'name' => 'finish',
                ),
        );
        //$workflow->acceptance($test);
    }

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
       // $status = $workflow->getStatus('second_state');
       // $this->isInstanceOf('Mothership\StateMachine\StatusInterface', $status);

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
    public function workflowGoodProvidser()
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
     * @expectedException     Mothership\StateMachine\Exception\WorkflowException
     */
    public function testMethodNotImplementedException()
    {
        $workflow_class = "Mothership\\StateMachine\\Examples\\Fail\\FailWorkflow";
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