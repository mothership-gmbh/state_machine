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

/**
 * Interface TransitionInterface
 *
 * @category  Mothership
 * @package   Mothership_State_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2015 Mothership GmbH
 * @link      http://www.mothership.de/
 */
interface TransitionInterface
{
    public function __construct(StatusInterface $status, $transition_from);

    /**
     * Execute the transiction
     * @return mixed
     */
    public function process();

    /**
     * Get the starting point of the transiction
     * @return mixed
     */
    public function getTransitionFrom();

    /**
     * Is the name of the transiction  and the name of the new state a<nd also the method will be execute in the
     * workflow...
     * @return mixed
     */
    public function getName();

    /**
     * ending point of the transiction
     * @return mixed
     */
    public function getTransitionTo();

    /**
     * method that will be execute in the workflow
     * @return mixed
     */
    public function getMethodToRun();

    /**
     * If the transition has a condition to be executed
     * @return bool
     */
    public function hasCondition();

    /**
     * Get the condition to be executed
     * @return mixed
     */
    public function getCondition();
}

