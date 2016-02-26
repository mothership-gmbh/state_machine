<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

/**
 * Class Transition.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
class Transition implements TransitionInterface
{
    /**
     * @var \Mothership\StateMachine\StatusInterface
     */
    protected $status;

    /**
     * @var  string
     */
    protected $transition_from;

    /**
     * @var bool
     */
    protected $hasCondition;

    /**
     * @var  description
     */
    protected $condition;

    /**
     * @param StatusInterface $status
     * @param                 $transition_from
     */
    public function __construct(StatusInterface $status, $transition_from)
    {
        $this->status = $status;
        if (!is_array($transition_from)) {
            $this->transition_from = $transition_from;
            $this->hasCondition    = false;
            $this->condition       = null;
        } else {
            $this->transition_from = $transition_from['status'];
            $this->hasCondition    = true;
            $this->condition       = $transition_from['result'];
        }
    }

    /**
     * Returns the state resulting of this transition.
     *
     * @return StatusInterface
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the starting point of the transiction.
     *
     * @return mixed
     */
    public function getFrom()
    {
        return $this->transition_from;
    }

    /**
     * If the transition has a condition to be executed.
     *
     * @return bool
     */
    public function hasCondition()
    {
        return $this->hasCondition;
    }

    /**
     * Get the condition to be executed.
     *
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
