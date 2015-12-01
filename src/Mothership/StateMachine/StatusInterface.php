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
 * Interface StatusInterface
 *
 * @category  Mothership
 * @package   Mothership_State_machine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2015 Mothership GmbH
 * @link      http://www.mothership.de/
 */
interface StatusInterface
{
    /**
     * Returns the state name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the state type
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the available transitions
     *
     * @return array
     */
    public function getTransitions();

    /**
     * Check if a transiction can be run from this status
     * @param $transition_name
     * @param StatusInterface $status_from
     * @return mixed
     */
    public function execute($transition_name, StatusInterface $status_from);

    /**
     * @param string $property
     *
     * @return boolean
     */
    public function has($property);

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function get($property);

    /**
     * Returns optional state properties
     *
     * @return mixed
     */
    public function getProperties();

    /**
     * @return WorkflowInterface
     */
    public function getWorkflow();

    /**
     * This is an internal status for the State, valid if the next step need a condition to be executed
     * @return mixed
     */
    public function setInternalStatus($state);
}

