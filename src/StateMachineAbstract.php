<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

use Mothership\StateMachine\Exception\StateMachineException;
use Mothership\StateMachine\Exception\WorkflowException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractMagentoCommand.
 *
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
abstract class StateMachineAbstract implements StateMachineInterface
{
    /**
     * @var null|string
     */
    protected $workflowFile = null;

    protected $workflow_array;

    /**
     * @var \Mothership\StateMachine\WorkflowAbstract
     */
    protected $workflow;

    /**
     * @param string $file
     *
     * @throws StateMachineException
     */
    public function __construct($file = null)
    {
        $this->workflowFile = $file;
        if (!file_exists($this->workflowFile) || is_null($file)) {
            throw new StateMachineException(
                "File " . $this->workflowFile . "  doesn't exist or null, you must provide an existing workflow YAML file", 100, null
            );
        }
        //read the file
        try {
            $this->workflow_array = $this->parseYAML();
            if ($this->workflow_array === false || $this->workflow_array === null) {
                throw new StateMachineException("Error parsing " . $this->workflowFile . " file", 98, null);
            }
        } catch (\Symfony\Component\Yaml\Exception\ParseException $ex) {
            throw new StateMachineException("Error parsing " . $this->workflowFile . " file", 98, $ex);
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
            $yaml                = Yaml::parse(file_get_contents($this->workflowFile));
            $yaml_fixed          = [];
            $yaml_fixed['class'] = $yaml['class'];
            foreach ($yaml['states'] as $key => $value) {
                if ($value['type'] != 'initial') {
                    $state                  = [
                        'name'             => $key,
                        'type'             => $value['type'],
                        'transitions_from' => $value['transitions_from'],
                        'transitions_to'   => $value['transitions_to']
                    ];
                    $yaml_fixed['states'][] = $state;
                } else {
                    $state                  = [
                        'name' => $key,
                        'type' => $value['type']
                    ];
                    $yaml_fixed['states'][] = $state;
                }
            }

            return $yaml_fixed;
        } catch (\Symfony\Component\Yaml\Exception\ParseException $ex) {
            throw $ex;
        }
    }

    /**
     * Create the workflow
     *
     * @throws \Mothership\StateMachine\Exception\StateMachineException
     *
     * @return void
     */
    protected function initWorkflow()
    {
        $className = $this->workflow_array['class']['name'];

        if (!class_exists($className, true)) {
            throw new StateMachineException('The class ' . $className . ' does not exist!', 100);
        }

        try {
            $this->workflow = new $className($this->workflow_array);
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

        $_transitions = [];
        foreach ($this->workflow_array['states'] as $state) {
            if (array_key_exists("transitions_from", $state)) {
                $transitions_from = $state['transitions_from'];
                foreach ($transitions_from as $from) {
                    if (is_array($from)) {
                        $_transitions[] = sprintf(
                            $pattern,
                            $from['status'],
                            $state['name'],
                            "<< IF "
                            . $this->convertToStringCondition($from['result']) . " >>" . $state['name']
                        );
                    } else {
                        $_transitions[] = sprintf($pattern, $from, $state['name'], $state['name']);
                    }
                }
            }
        }
        file_put_contents('/tmp/sm.gv', sprintf($template, count($_transitions) * 2, implode("\n", $_transitions)));
        shell_exec('dot -Tpng /tmp/sm.gv -o ' . $outputPath);

        if ($stopAfterExecution) {
            //exit;
        }
    }

    /**
     * Run the state machine with optional arguments
     *
     * @param array $args
     * @param bool  $enableLog
     *
     * @return mixed
     *
     * @throws StateMachineException
     */
    public function run(array $args = [], $enableLog = false)
    {
        try {
            return $this->workflow->run($args, $enableLog);
        } catch (WorkflowException $ex) {
            throw new StateMachineException("Error running State Machine", 100, $ex);
        }
    }

    /**
     * Wrapper for running the acceptance test
     *
     * @param mixed $states
     * @param bool  $verbose
     *
     * @return void
     */
    public function acceptance($states, $verbose = false)
    {
        $this->workflow->acceptance($states, $verbose);
    }

    /**
     * Retreive the workflow implementation
     *
     * @return \Mothership\StateMachine\WorkflowAbstract
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Internal method required for the rendering.
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

