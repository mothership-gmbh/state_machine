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
 * @package   Mothership_state_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright Copyright (c) 2015 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */
use Mothership\StateMachine\Exception\StatusException;
use Mothership\StateMachine\Exception\TransitionException;
use Mothership\StateMachine\Exception\WorkflowException;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WorkflowAbstract
 *
 * @category  Mothership
 * @package   Mothership_State_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2015 Mothership GmbH
 * @link      http://www.mothership.de/
 */
abstract class WorkflowAbstract implements WorkflowInterface
{
    /**
     * Usefull variables for the object passed throw workflow configuration file
     *
     * @var array
     */
    protected $vars = [];
    /**
     * @var mixed[StatusInterface];
     */
    protected $states = [];
    /**
     * @var \Mothership\StateMachine\StatusInterface
     */
    protected $current_status;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $output;

    /**
     * Assign all arguments to keys
     *
     * @param array $args
     *
     * @throws WorkflowException
     */
    public function __construct(array $args = [])
    {
        foreach ($args as $key => $value) {
            $this->vars[$key] = $value;
        }

        $this->_init();
    }

    /**
     * Return the variables which are set via the $args parameter
     *
     * @param null|string $key
     *
     * @return array|null
     */
    public function getArgs($key = null)
    {
        if (null === $key) {
            return $this->args;
        }

        if (!isset($this->args[$key])) {
            return null;
        }

        return $this->args[$key];
    }

    /**
     * Get the output for the workflow
     *
     * @return \Symfony\Component\Console\Output\ConsoleOutput
     */
    public function getOutput()
    {
        return $this->outpout;
    }

    /**
     * @throws WorkflowException
     */
    protected function _init()
    {
        if (!array_key_exists("states", $this->vars)) {
            throw new WorkflowException("You must define some states:\n", 99, null);
        }

        //check if all the methods for each status is callable
        $methods_not_implemented = "";
        try {
            foreach ($this->vars['states'] as $status) {
                array_push($this->states, new Status($this, $status));

                /**
                 * The initial state will never be executed but only the transitions, therefore it will be excluded
                 * from the list of methods which must be implemented
                 */
                if (!method_exists($this, $status['name']) && $status['type'] != 'initial') {
                    $methods_not_implemented .= $status['name'] . "\n";
                }
            }
        } catch (StatusException $ex) {
            throw new WorkflowException("Error in one state of the workflow:\n" . $ex->getMessage(), 79);
        }


        if (strlen($methods_not_implemented) > 0) {
            throw new WorkflowException(
                "This methods are not implemented in the workflow:\n" .
                $methods_not_implemented, 79, null
            );
        }

        $this->setInitialState();
    }

    /**
     * The initial state must always be 'initial'
     *
     * @return StatusInterface
     *
     * @throws WorkflowException
     */
    function setInitialState()
    {
        foreach ($this->states as $status) {
            if ($status->getType() == 'initial') {
                $this->current_status = $status;

                return $status;
            }
        }
        throw new WorkflowException("No initial state found for the workflow", 90, null);
    }

    /**
     * @param $transition_name
     *
     * @return mixed|void
     *
     * @throws WorkflowException
     */
    protected function executeTransition($transition_name)
    {
        try {
            //echo "\ntransit to " . $transition_name;
            $status = $this->getStatus($transition_name);

            return $status->execute($transition_name, $this->current_status);
        } catch (StatusException $ex) {
            if ($ex->getGravity() > 50) {
                throw new WorkflowException("Error executing the transition", 100, $ex, null);
            }

            return false;
        } catch (TransitionException $ex) {
            throw new WorkflowException("Error executing the transition", 100, $ex, null);
        }
    }

    /**
     * Return the current status of the workflow
     *
     * @return \Mothership\StateMachine\StatusInterface $status
     */
    function getCurrentStatus()
    {
        return $this->current_status;
    }

    /**
     * Set the status of the workflow
     *
     * @param \Mothership\StateMachine\StatusInterface $status
     *
     * @return mixed
     */
    function setState(StatusInterface $status)
    {
        $this->current_status = $status;
    }

    /**
     * Get the status of the workflow by its name
     *
     * @param $name
     *
     * @return \Mothership\StateMachine\StatusInterface WorkflowException
     * @throws WorkflowException
     */
    function getStatus($name)
    {
        foreach ($this->states as $status) {
            if ($status->getName() == $name) {
                return $status;
            }
        }
        throw new WorkflowException("No status found with the name " . $name, 70, null);
    }

    protected $log;

    protected function log($state, $return = null)
    {
        $data['name'] = $state;

        if ($state == 'do_it_again') {
            $t = '';
        }

        if (null !== $return) {
            $data['return'] = (bool) $return;
        }
        $this->log[] = $data;
    }

    /**
     *
     * @throws \Exception
     */
    public function run($report = false)
    {
        $nextState = $this->current_status->getName();

        $continueExecution = true;

        // execute the current workflow
        while (true === $continueExecution) {


            if ($nextState == 'finish') {
                $continueExecution = false;
            }

            // condition is the return value of a method
            $condition = call_user_func_array([$this, $nextState], []);

            //
            $this->log($nextState, $condition);

            /**
             * Skip the previous execution if the previous execution
             * is finished
             */
            if (false === $continueExecution) continue;

            $nextState = $this->getNextStateFrom($nextState, $condition);
        }

        $this->acceptance($this->log);
    }

    /**
     * Check if the current automata can run in a given order
     *
     * @param array $states
     *
     */
    public function acceptance(array $states = [])
    {
        if (count($states) < 2) {
            throw new \Exception('Automata needs at least two states');
        }

        foreach ($states as $index => $state) {

            $condition = (array_key_exists('return', $state)) ? $state['return'] : null;

            if ($index + 1 == count($states) || $state['name'] == 'finish') continue;

            $nextState = $this->getNextStateFrom($state['name'], $condition);

            $message = sprintf("δ: (C × Z[%d] → Z[%d) = [%s] x [%s] → [%s] ", $this->getStatusIndex($state['name']), $this->getStatusIndex($states[$index + 1]['name']), var_export($condition, true), $state['name'], $states[$index + 1]['name']);
            if ($nextState !== $states[$index + 1]['name']) {
                throw new \Exception('Error. Invalid transitions. Last transition: ' . $message);
            } else {
                echo "\n" . $message;
            }
        }
    }

    /**
     * Get the position of a state
     *
     * @param $statusname
     *
     * @return int
     */
    private function getStatusIndex($statusname)
    {
        $status_count = count($this->states);
        for ($i = 0; $i < $status_count; $i++) {
            if ($this->states[$i]->getName() == $statusname) {
                return $i;
            }
        }
    }

    /**
     * Iterates all nodes
     */
    protected function getNextStateFrom($currentTransition, $condition = null)
    {
        $possibleTransition = null;
        foreach ($this->states as $statusIndex => $status) {

            if (empty($status->getTransitions())) {
                continue;
            }
            foreach ($status->getTransitions() as $transition) {

                // FIX transiction from
                if ($currentTransition == $transition->getTransitionFrom()) {

                    /**
                     * If the next expected transition depends on a condition,
                     * we need to check, if the condition is also set
                     */
                    if (true === $transition->hasCondition() && $condition == $transition->getCondition()) {
                        return $transition->getName();
                    }

                    if (false === $transition->hasCondition()) {
                        return $transition->getName();
                    }
                }
            }
        }
        $error = "\nδ: (X × Z → Z) ". sprintf("[%s] x [%s] → [%s] ", $condition, $currentTransition, 'NULL');
        throw new TransitionException($error);
    }
}