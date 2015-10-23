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
    public function testGoodWorkflow($worklowClass, $output, $arguments)
    {
        $workflow = new $worklowClass($output, $arguments);
        $this->isInstanceOf($worklowClass,$workflow);
        /**
         * get CurrentStatus
         */
        $status = $workflow->getCurrentStatus();
        $this->isInstanceOf('Mothership\StateMachine\StatusInterface',$status);
        /**
         * getStatus
         */
        $status = $workflow->getStatus('second_state');
        $this->isInstanceOf('Mothership\StateMachine\StatusInterface',$status);
        /**
         * run
         */
        $this->assertTrue($workflow->run());
    }
    /**
     * @dataProvider workflowFailProvider
     * @expectedException     Mothership\Exception\StateMachine\WorkflowException
     */
    public function testExceptionInConstructor($worklowClass, $output, $arguments)
    {
        $workflow = new $worklowClass($output, $arguments);
    }

    public function workflowGoodProvider()
    {
        $this->state_machine_dir = $this->getExemplesDir();
        $workflow = [];
        foreach ($this->state_machine_dir as $dir) {
            array_push($workflow, [
                "Exemple\\" . $dir['NAME'] . "\\".$dir['NAME']."Workflow",
                new \Symfony\Component\Console\Output\ConsoleOutput(),
                $this->parseYml($dir['PATH'].'Workflow.yml'),
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
                "Exemple\\" . $dir['NAME'] . "\\".$dir['NAME']."Workflow",
                new \Symfony\Component\Console\Output\ConsoleOutput(),
                []
            ]);
            array_push($workflow, [
                "Exemple\\" . $dir['NAME'] . "\\".$dir['NAME']."Workflow",
                new \Symfony\Component\Console\Output\ConsoleOutput(),
                ['arg1' => 1, 'args2' => 2]
            ]);
        }
        return $workflow;
    }


    private function parseYml($file)
    {
        $yml = Yaml::parse(file_get_contents($file));
        $yml_fixed = [];
        foreach ($yml['states'] as $key => $value) {
            if ($value['type'] != 'initial') {
                $state = ['name' => $key,
                    'type' => $value['type'],
                    'transitions_from' => $value['transitions_from'],
                    'transitions_to' => $value['transitions_to']];
                $yml_fixed['states'][] = $state;
            } else {
                $state = ['name' => $key,
                    'type' => $value['type']];
                $yml_fixed['states'][] = $state;
            }
        }

        return $yml_fixed;
    }
}