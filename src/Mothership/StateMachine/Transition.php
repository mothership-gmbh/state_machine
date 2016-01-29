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
use Mothership\StateMachine\Exception\TransitionException;
use Mothership\StateMachine\Exception\WorkflowException;

/**
 * Class Transition
 *
 * @category  Mothership
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 * @link      http://www.mothership.de/
 */
class Transition implements TransitionInterface
{

    protected $name;
    protected $status;
    protected $transition_from;
    protected $hasCondition;
    protected $condition;

    /**
     *
     *
     * @param StatusInterface $status
     * @param                 $transition_from
     */
    public function __construct(StatusInterface $status, $transition_from)
    {
        $this->status = $status;
        $this->name   = $status->getName();
        if (!is_array($transition_from)) {
            $this->transiction_from = $transition_from;
            $this->hasCondition = false;
            $this->condition = null;
        } else {
            $this->transiction_from = $transition_from['status'];
            $this->hasCondition = true;
            $this->condition = $transition_from['result'];
        }
    }

    /**
     * Returns the state resulting of this transition
     *
     * @return StatusInterface
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     *
     * @throws TransitionException
     */
    public function process()
    {
        try {
            $method = $this->getMethodToRun();

            if (method_exists($this->getStatus()->getWorkflow(), '_preDispatch')) {
                $methodPre = '_preDispatch';
                $this->getStatus()->getWorkflow()->$methodPre($method);
            }

            $result = $this->getStatus()->getWorkflow()->$method();

            if (method_exists($this->getStatus()->getWorkflow(), '_postDispatch')) {
                $methodPre = '_postDispatch';
                $this->getStatus()->getWorkflow()->$methodPre($method);
            }

            $this->getStatus()->setInternalStatus($result);
            return $this->getStatus();
        } catch (WorkflowException $ex) {
            throw new TransitionException("error processing transiction " . $this->getName(), 100, $ex);
        }
    }

    /**
     * Returns the name of the transition
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the starting point of the transiction
     *
     * @return mixed
     */
    public function getTransitionFrom()
    {
        return $this->transiction_from;
    }

    /**
     * ending point of the transiction
     * @return mixed
     */
    public function getTransitionTo()
    {
        return $this->name;
    }

    /**
     * method that will be execute in the workflow
     * @return mixed
     */
    public function getMethodToRun()
    {
        /**
         * @todo if we want to have different switch we must parametrize in this point
         */
        return $this->name;
    }

    /**
     * If the transition has a condition to be executed
     * @return bool
     */
    public function hasCondition()
    {
        return $this->hasCondition;
    }

    /**
     * Get the condition to be executed
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }
}

