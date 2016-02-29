<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

/**
 * Interface TransitionInterface.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
interface TransitionInterface
{
    public function __construct(StatusInterface $status, $transitionFrom);

    /**
     * Get the starting point of the transition.
     *
     * @return mixed
     */
    public function getFrom();

    /**
     * If the transition has a condition to be executed.
     *
     * @return bool
     */
    public function hasCondition();

    /**
     * Get the condition to be executed.
     *
     * @return mixed
     */
    public function getCondition();
}
