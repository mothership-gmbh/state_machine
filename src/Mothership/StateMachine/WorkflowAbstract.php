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
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright Copyright (c) 2015 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */

namespace Mothership\StateMachine;

use Mothership\StateMachine\Exception\StatusException;
use Mothership\StateMachine\Exception\TransictionException;
use Mothership\StateMachine\Exception\WorkflowException;
use Mothership\StateMachine\WorkflowInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use \Symfony\Component\Console\Output\OutputInterface;
use Mothership\StateMachine\StatusInterface;

abstract class WorkflowAbstract implements WorkflowInterface
{
    /**
     * Usefull variables for the object passed throw workflow configuration file
     * @var array
     */
    protected $vars = [];
    /**
     * @var StatusInterface;
     */
    protected $states = [];
    /**
     * @var \Mothership\StateMachine\StatusInterface
     */
    protected $current_status;

    public function __construct(array $args = [])
    {
        foreach ($args as $key => $value) {
            $this->vars[$key] = $value;
        }

        $this->_init();
    }

    /**
     * Get the output for the workflow
     * @return OutputInterface
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
        try{
            foreach ($this->vars['states'] as $status) {
                array_push($this->states, new Status($this, $status));
                if (!method_exists($this, $status['name'])) {
                    $methods_not_implemented .= $status['name'] . "\n";
                }
            }
        }catch (StatusException $ex)    {
            throw new WorkflowException("Error in one state of the workflow:\n" .$ex->getMessage(), 79);
        }

        if (strlen($methods_not_implemented) > 0) {
            throw new WorkflowException("This methods are not implemented in the workflow:\n" .
                $methods_not_implemented, 79, null);
        }

        $this->setInitialState();
    }

    /**
     * @return StatusInterface
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
     * @param $transiction_name
     * @return mixed|void
     * @throws WorkflowException
     */
    protected function executeTransition($transiction_name)
    {
        try {
            $status = $this->getStatus($transiction_name);
            return $status->execute($transiction_name, $this->current_status);
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
     * @return \Mothership\StateMachine\StatusInterface $status
     */
    function getCurrentStatus()
    {
        return $this->current_status;
    }

    /**
     * Set the status of the workflow
     * @param \Mothership\StateMachine\StatusInterface $status
     * @return mixed
     */
    function setState(StatusInterface $status)
    {
        $this->current_status = $status;
    }

    /**
     * Get the status of the workflow by its name
     * @param $name
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

    /**
     * execute the workflow
     */
    public function run()
    {
        $states_count = count($this->states);
        for ($i = 1; $i < $states_count; $i++) {
            $transations = $this->states[$i]->getTransitions();
            foreach ($transations as $t) {
                try {
                    $status = $this->executeTransition($t->getName());
                    if ($status !== false) {
                        $this->current_status = $status;
                        $changeStatus = $this->checkIfPreviousTransition($status);
                        if ($changeStatus !== false) {
                            $i = $this->getStatusIndex($changeStatus);
                            break;
                        }
                    }
                } catch (TransitionException $ex) {
                    new WorkflowException("Error during workflow->run()", 100, $ex, $this->output);
                } catch (WorkflowException $ex) {
                    new WorkflowException("Error during workflow->run()", 100, $ex, $this->output);
                } catch (StateException $ex) {
                    if ($this->current_status->hasInternalState()) {
                        $i = 1;
                        break;
                    }
                }

            }
        }
        return true;
    }

    /**
     * Get the position of a state
     * @param $statusname
     * @return int
     */
    private function getStatusIndex($statusname)
    {
        $status_count = count($this->states);
        for ($i = 0; $i < $status_count; $i++) {
            if ($this->states[$i]->getName() == $statusname) {
                return $i - 1;
            }
        }
    }

    /**
     * Check if there is a previous transition that could be executed from $status
     * @param \Mothership\StateMachine\StatusInterface $status
     * @return bool|string false or the name of the status to execute
     */
    private function checkIfPreviousTransition(StatusInterface $status)
    {
        $lastIndex = $this->getStatusIndex($status->getName());
        for ($i = 0; $i < $lastIndex; $i++) {
            $transictions = $this->states[$i]->getTransitions();
            foreach ($transictions as $t) {
                if ($t->getTransitionFrom() == $status->getName()) {
                    return $this->states[$i]->getName();
                }
            }
        }
        return false;
    }

}
