<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

use Mothership\StateMachine\Exception\StatusException;
use Mothership\StateMachine\Exception\TransitionException;
use Mothership\StateMachine\Exception\WorkflowException;

/**
 * Class WorkflowAbstract.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2015 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
abstract class WorkflowAbstract implements WorkflowInterface
{
    protected $args = [];

    /**
     * Usefull variables for the object passed throw workflow configuration file.
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
    protected $currentStatus;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $output;

    /**
     * Assign all arguments to keys.
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

        $this->initializeStates();
    }

    /**
     * Return the variables which are set via the $args parameter.
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
            return;
        }

        return $this->args[$key];
    }

    /**
     * Initialize the states
     *
     * @throws WorkflowException
     *
     * @return void
     */
    protected function initializeStates()
    {
        if (!array_key_exists('states', $this->vars)) {
            throw new WorkflowException("You must define some states:\n", 99, null);
        }

        //check if all the methods for each status is callable
        $methods_not_implemented = '';
        try {
            foreach ($this->vars['states'] as $status) {
                array_push($this->states, new Status($status));

                /*
                 * The initial state will never be executed but only the transitions, therefore it will be excluded
                 * from the list of methods which must be implemented
                 */
                if (!method_exists($this, $status['name']) && $status['type'] != 'initial') {
                    $methods_not_implemented .= $status['name']."\n";
                }
            }
        } catch (StatusException $ex) {
            throw new WorkflowException("Error in one state of the workflow:\n".$ex->getMessage(), 79);
        }

        if (strlen($methods_not_implemented) > 0) {
            throw new WorkflowException(
                "This methods are not implemented in the workflow:\n".
                $methods_not_implemented, 79, null
            );
        }
    }

    /**
     * The initial state must always be 'initial'.
     *
     * @return StatusInterface
     *
     * @throws WorkflowException
     */
    public function reset()
    {
        $this->setInitialState();
        $this->log = [];

    }

    /**
     * Set the initial state
     *
     * @return void
     */
    public function setInitialState()
    {
        foreach ($this->states as $status) {
            if ($status->getType() == StatusInterface::TYPE_INITIAL) {
                $this->currentStatus = $status;

                return;
            }
        }
        throw new WorkflowException('No initial state found for the workflow', 90, null);
    }

    /**
     * Return the current status of the workflow.
     *
     * @return \Mothership\StateMachine\StatusInterface $status
     */
    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    /**
     * Set the status of the workflow.
     *
     * @param \Mothership\StateMachine\StatusInterface $status
     *
     * @return mixed
     */
    public function setState(StatusInterface $status)
    {
        $this->currentStatus = $status;
    }

    /**
     * Get the status of the workflow by its name.
     *
     * @param $name
     *
     * @return \Mothership\StateMachine\StatusInterface WorkflowException
     *
     * @throws WorkflowException
     */
    public function getStatus($name)
    {
        foreach ($this->states as $status) {
            if ($status->getName() == $name) {
                return $status;
            }
        }
        throw new WorkflowException('No status found with the name '.$name, 70, null);
    }

    protected $log;

    /**
     * The log will create.
     *
     * @param      $state
     * @param null $return
     *
     * @return mixed
     */
    protected function addToLog($state, $return = null)
    {
        $data['name'] = $state;

        if (null !== $return) {
            $data['return'] = $return;
        }
        $this->log[] = $data;
    }

    /**
     * Get the log.
     *
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * The main method, which processes the state machine.
     *
     * @param mixed $args    You might pass external logic
     * @param bool  $saveLog If enabled, then all processed states will be
     *                       stored and can be processed in the acceptance method
     *                       later for debugging purpose
     *
     * @throws \Exception
     *
     * @return void|mixed
     */
    public function run($args = [],  $saveLog = false)
    {
        /**
         * The state machine must be able to re run the same processes again.
         */
        $this->reset();

        // just store the arguments for external logic
        $this->args = $args;

        $continueExecution = true;
        $nextState = $this->currentStatus;

        /**
         * Based on the initial state, the algorithm
         * will try to execute each method until the
         * final state is reached
         */
        while (true === $continueExecution) {

            if ($nextState->getType() == StatusInterface::TYPE_FINAL) {
                $continueExecution = false;
            }

            /*
             * Every workflow class has methods, which names are equal to the state names in the
             * configuration file. By executing the methods, a return value can be given. This
             * depends on your graph logic.
             *
             * However the return value will be seen as a condition for the NEXT state
             * transition evaluation.
             */
            $this->executeMethod('preDispatch');
            $condition = $this->executeMethod($nextState->getName());
            $this->executeMethod('postDispatch');

            if (true === $saveLog) {
                $this->addToLog($nextState->getName(), $condition);
            }

            /*
             * Mark the execution to be stopped when the next state
             * is StatusInterface::TYPE_FINAL.
             */
            if (false === $continueExecution) {
                continue;
            }

            $nextState = $this->getNextStateFrom($nextState->getName(), $condition);

            /*
             * Overwrite the current state. This does not affect
             * the application logic but will be used for debugging purpose to be able
             * to inspect the current state machine
             */
            $this->setState($nextState);
        }

        if (true === $saveLog) {
            return $this->log;
        }
    }

    /**
     * Helper method for executing.
     *
     * @param string $method
     *
     * @®eturn void
     */
    private function executeMethod($method, $args = [])
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        }
    }

    /**
     * Check if the current automata can run in a given order.
     *
     * @param array $states
     * @param bool  $verbose Print debug information
     *
     * @return bool
     */
    public function acceptance(array $states = [], $verbose = false)
    {
        if (count($states) < 2) {
            throw new \Exception('Automata needs at least two states');
        }

        foreach ($states as $index => $state) {
            $condition = (array_key_exists('return', $state)) ? $state['return'] : null;

            if ($index + 1 == count($states) || $state['name'] == 'finish') {
                continue;
            }

            $nextState = $this->getNextStateFrom($state['name'], $condition);

            $message = sprintf('δ: (C × Z[%d] → Z[%d) = [%s] x [%s] → [%s] ', $this->getStatusIndex($state['name']), $this->getStatusIndex($states[$index + 1]['name']), var_export($condition, true), $state['name'], $states[$index + 1]['name']);
            if ($nextState->getName() !== $states[$index + 1]['name']) {
                throw new \Exception('Invalid transition. Last transition: '.$message.' . Given: '.$nextState->getName());
            }

            if (true === $verbose) {
                $this->output->writeln($message);
            }
        }
        return true;
    }

    /**
     * Get the position of a state.
     *
     * @param $statusname
     *
     * @return int
     */
    private function getStatusIndex($statusname)
    {
        $status_count = count($this->states);
        for ($i = 0; $i < $status_count; ++$i) {
            if ($this->states[$i]->getName() == $statusname) {
                return $i;
            }
        }
    }

    /**
     * Try to get the next transition based on the current transition
     * and condition.
     *
     * There can only be one transition (Highlander mode)
     *
     * @param string          $currentTransition
     * @param int|string|bool $condition
     *
     * @return mixed
     *
     * @throws \Mothership\StateMachine\Exception\TransitionException
     */
    protected function getNextStateFrom($currentTransition, $condition = null)
    {
        $possibleTransition = null;

        /** @var \Mothership\StateMachine\Status $status */
        foreach ($this->states as $statusIndex => $status) {
            if (count($status->getTransitions()) == 0) {
                continue;
            }

            /* @var \Mothership\StateMachine\Transition $transition */
            foreach ($status->getTransitions() as $transition) {

                // FIX transiction from
                if ($currentTransition == $transition->getFrom()) {

                    /*
                     * If the next expected transition depends on a condition,
                     * we need to check, if the condition is also set
                     */
                    if (true === $transition->hasCondition() && $condition === $transition->getCondition()) {
                        return $transition->getStatus();
                    }

                    if (false === $transition->hasCondition()) {
                        return $transition->getStatus();
                    }
                }
            }
        }
        $error = "\nδ: (X × Z → Z) ".sprintf('[%s] x [%s] → [%s] ', $condition, $currentTransition, 'NULL');
        throw new TransitionException($error);
    }
}
