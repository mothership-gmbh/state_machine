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
use Mothership\StateMachine\StatusInterface;
use Mothership\StateMachine\Exception\StatusException;
use Mothership\StateMachine\TransitionInterface;
use Mothership\StateMachine\WorkflowInterface;
use Mothership\StateMachine\Transition;
/**
 * Class Status
 *
 * @category  Mothership
 * @package   Mothership_State_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2015 Mothership GmbH
 * @link      http://www.mothership.de/
 */
class Status implements StatusInterface
{

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var array
     */
    protected $types = [StatusInterface::TYPE_INITIAL, StatusInterface::TYPE_NORMAL, StatusInterface::TYPE_FINAL];

    /**
     * @var
     */
    protected $internalState;

    /**
     * @var array|mixed
     */
    protected $transitions = array();

    /**
     * @var \Mothership\StateMachine\WorkflowInterface
     */
    private $workflow;

    /**
     * @param \Mothership\StateMachine\WorkflowInterface $workflow
     * @param array                                      $properties
     *
     * @throws StatusException
     */
    public function __construct(WorkflowInterface $workflow, array $properties = [])
    {
        $this->workflow = $workflow;
        $this->properties = $properties;
        $this->transitions = $this->getTransitions();
    }

    /**
     * @return mixed
     *
     * @throws StatusException
     */
    public function getName()
    {
        if (!$this->has('name')) {
            throw new StatusException("Status without a name", 100, null);
        }
        return $this->properties['name'];
    }

    /**
     * @return mixed
     *
     * @throws StatusException
     */
    public function getType()
    {
        if (!$this->has('type')) {
            throw new StatusException("Status without a type", 100, null);
        }
        if (!in_array($this->properties['type'], $this->types)) {
            throw new StatusException("The type specified is invalid", 100, null);
        }
        return $this->properties['type'];
    }

    /**
     * Get if is an initial state
     *
     * @return bool
     *
     * @throws StatusException
     */
    protected function isInitialType()
    {
        return $this->getType() == 'initial';
    }

    /**
     * @return mixed
     *
     * @throws Exception
     * @throws StatusException
     */
    public function getTransitions()
    {
        if (count($this->transitions) == 0 && !$this->isInitialType()) {
            if (!$this->has('transitions_to')) {
                throw new StatusException("Status " . $this->getName() . " without transitions_to property", 80, null);
            }
            if (!$this->has('transitions_from')) {
                throw new StatusException("Status " . $this->getName() . " without transitions_from property", 81, null);
            }

            foreach ($this->properties['transitions_from'] as $transition) {
                array_push($this->transitions, new Transition($this, $transition));
            }

            if (count($this->transitions) == 0) {
                throw new StatusException("Status " . $this->getName() . " has transition_from property not maching the name",
                    85, null);
            }
        } else if ($this->isInitialType()) {


            $this->transitions = [];
        }
        return $this->transitions;
    }

    /**
     * @param $transition_name
     * @param \Mothership\StateMachine\StatusInterface $current_status
     *
     * @return bool
     *
     * @throws StatusException
     * @internal param \Mothership\StateMachine\TransictionInterface $transition
     */
    public function execute($transition_name, StatusInterface $current_status)
    {
        foreach ($this->transitions as $_transition) {
            /* @var $_transition Transition */

            if ($_transition->hasCondition()) {
                if ($_transition->getTransitionFrom() == $current_status->getName() && $_transition->getName() == $transition_name &&
                    $this->workflow->getStatus($current_status->getName())->getInternalStatus() == $_transition->getCondition()
                ) {
                    return $_transition->process();
                }
            } else {
                if ($_transition->getTransitionFrom() == $current_status->getName() && $_transition->getName() == $transition_name) {
                    return $_transition->process();
                }
            }
        }
        throw new StatusException("STATUS: [" . $transition_name . "] can't run from [" . $current_status->getName() . "]", 30, null);
    }

    /**
     * @param string $property
     *
     * @return boolean
     */
    public function has($property)
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * @param $property
     * @return mixed
     * @throws StatusException
     */
    public function get($property)
    {
        if (!$this->has($property)) {
            throw new StatusException("Status without a property: " . $property, 90, null);
        }
        return $this->properties[$property];
    }

    /**
     * Returns optional state properties
     *
     * @return mixed
     */
    public function getProperties()
    {
        return $this->getProperties();
    }

    /**
     * @return WorkflowInterface
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Set the internal status, useful if the next step need a condition to be executed
     *
     * @param $state
     *
     * @return mixed
     */
    public function setInternalStatus($state)
    {
        $this->internalState = $state;
    }

    /**
     * Get the internal status
     *
     * @return mixed
     */
    public function getInternalStatus()
    {
        return $this->internalState;
    }

    /**
     * @return bool
     */
    public function hasInternalState()
    {
        if (!isset($this->internalState) || !isnull($this->internalState)) {
            return true;
        }
        return false;
    }
}

