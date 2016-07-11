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
     * The original configuration for the state machine.
     *
     * @var mixed
     */
    protected $configuration;

    /**
     * The workflow configuration onlycontains the parsed
     * configuration
     *
     * @var mixed
     */
    protected $workflowConfiguration;

    /**
     * @var \Mothership\StateMachine\WorkflowAbstract
     */
    protected $workflow;

    /**
     * Pass the.
     *
     * @param array $configuration
     *
     * @throws StateMachineException
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
        if (empty($configuration)) {
            throw new StateMachineException('Empty configuration file', 98, null);
        }

        //read the file
        try {
            $this->workflowConfiguration = $this->parseYAML();
            if ($this->workflowConfiguration === false || $this->workflowConfiguration === null) {
                throw new StateMachineException('Error parsing the configuration', 98, null);
            }
        } catch (\Symfony\Component\Yaml\Exception\ParseException $ex) {
            throw new StateMachineException('Error parsing the configuration', 98, $ex);
        }

        $this->initWorkflow();
    }

    /**
     * Parse the yaml configuration.
     *
     * @return array
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \Exception
     */
    protected function parseYAML()
    {
        try {
            $yaml                = $this->configuration;
            $yaml_fixed          = [];
            $yaml_fixed['class'] = $yaml['class'];
            foreach ($yaml['states'] as $key => $value) {
                if ($value['type'] != \Mothership\StateMachine\StatusInterface::TYPE_INITIAL && $value['type'] != \Mothership\StateMachine\StatusInterface::TYPE_EXCEPTION) {
                    $state                  = [
                        'name'             => $key,
                        'type'             => $value['type'],
                        'transitions_from' => $value['transitions_from'],
                        'transitions_to'   => $value['transitions_to'],
                    ];
                    $yaml_fixed['states'][] = $state;
                } else {
                    $state                  = [
                        'name' => $key,
                        'type' => $value['type'],
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
     * Create the workflow.
     *
     * @throws \Mothership\StateMachine\Exception\StateMachineException
     */
    protected function initWorkflow()
    {
        $className = $this->workflowConfiguration['class']['name'];

        if (!class_exists($className, true)) {
            throw new StateMachineException('The class ' . $className . ' does not exist!', 100);
        }

        try {
            $this->workflow = new $className($this->workflowConfiguration);
        } catch (WorkflowException $ex) {
            throw new StateMachineException('Workflow with some problems', 90, $ex);
        }
    }

    /**
     * create a graph for the state machine.
     *
     * @param string     $outputPath         relative path of the generated image
     * @param bool|false $stopAfterExecution if we want to exit after graphic generation
     *
     * @return void
     */
    public function renderGraph($outputPath = './workflow.png', $stopAfterExecution = true)
    {
        /*
         * This example is based on http://martin-thoma.com/how-to-draw-a-finite-state-machine/
         * Feel free to tweak the rendering output. I have decided do use the most simple
         * implementation over the fancy stuff to avoid additional complexity.
         */
        $template
            = '
            digraph finite_state_machine {
                rankdir=LR;
                size="%d"

                node [shape = doublecircle]; start;
                node [shape = circle];

                %s
            }
        ';

        $pattern = ' %s  -> %s [ label = "%s" ];';

        $_transitions = [];
        foreach ($this->workflowConfiguration['states'] as $state) {
            if (array_key_exists('transitions_from', $state)) {
                $transitions_from = $state['transitions_from'];
                foreach ($transitions_from as $from) {
                    if (is_array($from)) {
                        $_transitions[] = sprintf(
                            $pattern,
                            $from['status'],
                            $state['name'],
                            '<< IF '
                            . $this->convertToStringCondition($from['result']) . ' >>' . $state['name']
                        );
                    } else {
                        $_transitions[] = sprintf($pattern, $from, $state['name'], $state['name']);
                    }
                }
            } else {
                if ('type' == 'exception') {
                    $_transitions[] = 'node [shape = doublecircle]; exception;';
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
     * Run the state machine with optional arguments.
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
            throw new StateMachineException('Error running State Machine', 100, $ex);
        }
    }

    /**
     * Wrapper for running the acceptance test.
     *
     * @param mixed $states
     * @param bool  $verbose
     */
    public function acceptance($states, $verbose = false)
    {
        $this->workflow->acceptance($states, $verbose);
    }

    /**
     * Retreive the workflow implementation.
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
                return 'TRUE';
            } else {
                return 'FALSE';
            }
        }

        return (string) $condition;
    }
}
