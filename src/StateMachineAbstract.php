<?php
namespace Mothership\StateMachine;
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
use Mothership\StateMachine\Exception\StateMachineException;
use Mothership\StateMachine\Exception\WorkflowException;
use Symfony\Component\Yaml\Yaml;
/**
 * Class StateMachineAbstract
 *
 * @category  Mothership
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 * @link      http://www.mothership.de/
 */
abstract class StateMachineAbstract implements StateMachineInterface
{
    /**
     * @var null|string
     */
    protected $workflow_file;
    protected $workflow_array;
    protected $workflow;

    /**
     * @param string $file
     *
     * @throws StateMachineException
     */
    public function __construct($file = null)
    {
        $this->workflow_file = $file;
        if (!file_exists($this->workflow_file) || is_null($file)) {
            throw new StateMachineException("File " . $this->workflow_file . "  doesn't exist or null, you
            must provide an existing workflow YAML file",
                100, null);
        }
        //read the file
        try {
            $this->workflow_array = $this->parseYAML();
            if ($this->workflow_array === false || $this->workflow_array === null) {
                throw new StateMachineException("Error parsing " . $this->workflow_file . " file", 98, null);
            }
        } catch (Symfony\Component\Yaml\Exception\ParseException $ex) {
            throw new StateMachineException("Error parsing " . $this->workflow_file . " file", 98, $ex);
        }

        $this->initWorkflow();
    }

    /**
     * Parse the yaml file
     *
     * @return array
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \Exception
     */
    protected function parseYAML()
    {
        try {
            $yaml = Yaml::parse(file_get_contents($this->workflow_file));
            $yaml_fixed = [];
            $yaml_fixed['class'] = $yaml['class'];
            foreach ($yaml['states'] as $key => $value) {
                if ($value['type'] != 'initial') {
                    $state = ['name' => $key,
                        'type' => $value['type'],
                        'transitions_from' => $value['transitions_from'],
                        'transitions_to' => $value['transitions_to']];
                    $yaml_fixed['states'][] = $state;
                } else {
                    $state = ['name' => $key,
                        'type' => $value['type']];
                    $yaml_fixed['states'][] = $state;
                }
            }

            return $yaml_fixed;
        } catch (Symfony\Component\Yaml\Exception\ParseException $ex) {
            throw $ex;
        }
    }

    /**
     * Create the instance of the real workflow
     *
     * @return void
     */
    protected function initWorkflow()
    {
        try {
            $class_name = $this->workflow_array['class']['name'];
            $this->workflow = new $class_name($this->workflow_array);
        } catch (WorkflowException $ex) {
            throw new StateMachineException("Workflow with some problems", 90, $ex);
        }
    }

    /**
     * create a graph for the state machine
     *
     * @param string     $outputPath         relative path of the generated image
     * @param bool|false $stopAfterExecution if we want to exit after graphic generation
     *
     * @return void
     */
    public function renderGraph($outputPath = './workflow.png', $stopAfterExecution = true)
    {

        /**
         * This example is based on http://martin-thoma.com/how-to-draw-a-finite-state-machine/
         * Feel free to tweak the rendering output. I have decided do use the most simple
         * implementation over the fancy stuff to avoid additional complexity.
         */
        $template
            = "
            digraph finite_state_machine {
                rankdir=LR;
                size=\"%d\"

                node [shape = doublecircle]; start;
                node [shape = circle];

                %s
            }
        ";

        $pattern = " %s  -> %s [ label = \"%s\" ];";

        $_transitions = array();
        foreach ($this->workflow_array['states'] as $state) {
            if (array_key_exists("transitions_from", $state)) {
                $transitions_from = $state['transitions_from'];
                foreach ($transitions_from as $from) {
                    if (is_array($from)) {
                        $_transitions[] = sprintf($pattern, $from['status'], $state['name'], "<< IF "
                            . $this->convertToStringCondition($from['result']) . " >>" . $state['name']);
                    } else {
                        $_transitions[] = sprintf($pattern, $from, $state['name'], $state['name']);
                    }
                }
            }
        }
        file_put_contents('/tmp/sm.gv', sprintf($template, count($_transitions) * 2, implode("\n", $_transitions)));
        shell_exec('dot -Tpng /tmp/sm.gv -o ' . $outputPath);

        if ($stopAfterExecution) {
            exit;
        }
    }

    /**
     * Run the state machine with optional arguments
     *
     * @param array $args
     *
     * @return mixed
     *
     * @throws StateMachineException
     */
    public function run(array $args = [])
    {
        try {
            return $this->workflow->run($args);
        } catch (WorkflowException $ex) {
            throw new StateMachineException("Error running State Machine", 100, $ex);
        }
    }

    /**
     * Convert the Condition to string
     *
     * @param $condition
     *
     * @return string
     */
    private function convertToStringCondition($condition)
    {
        if (is_bool($condition)) {
            if ($condition) {
                return "TRUE";
            } else {
                return "FALSE";
            }
        }

        return (string) $condition;
    }
}

