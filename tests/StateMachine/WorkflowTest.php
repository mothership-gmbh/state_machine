<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mothership
 * @package   Mothership_{EXTENSION NAME}
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright Copyright (c) 2015 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */
use Symfony\Component\Yaml\Yaml;

use Exemple\Simple\SimpleWorkflow;
use Exemple\IfConditions\IfConditionsWorkflow;

class WorkflowTest extends MothershipBaseTestCase
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
     * @expectedException     Mothership\Exception\StateMachine\WorkflowException
     */
    public function testExceptionInConstructor($worklowClass, $arguments)
    {
        $workflow = new $worklowClass($arguments);
    }

    public function workflowGoodProvider()
    {
        $this->state_machine_dir = $this->getExemplesDir();
        $workflow = [];
        foreach ($this->state_machine_dir as $dir) {
            $state_machine_class = "Exemple\\" . $dir['NAME'] . "\\" . $dir['NAME'] ."StateMachine";
            $state_machine = new $state_machine_class($dir['PATH'] . 'Workflow.yml');
            array_push($workflow, [
                "Exemple\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "Workflow",
                $this->invokeMethod($state_machine,"parseYAML"),
            ]);
        }
        return $workflow;
    }

    public function workflowFailProvider()
    {
        $this->state_machine_dir = $this->getExemplesDir();
        $workflow = [];

        foreach ($this->state_machine_dir as $dir) {
            array_push($workflow, [
                "Exemple\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "Workflow",
                []
            ]);
            array_push($workflow, [
                "Exemple\\" . $dir['NAME'] . "\\" . $dir['NAME'] . "Workflow",
                ['arg1' => 1, 'args2' => 2]
            ]);
        }
        return $workflow;
    }



    /**
     * @dataProvider    workflowGoodProvider
     */
    public function testVars($worklowClass, $arguments)
    {
        $workflow = new $worklowClass($arguments);
        $vars = $this->getPropertyValue($workflow,"vars");
        $this->assertArrayHasKey('states', $vars);
        $this->assertArrayHasKey('class', $vars);
        $this->assertArrayHasKey('args', $vars['class']);

    }
}