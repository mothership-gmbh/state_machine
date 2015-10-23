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

use Mothership\StateMachine\StatusInterface;
use Mothership\StateMachine\TransictionInterface;

interface WorkflowInterface
{
    /**
     * Set the inizial state of the workflow
     * @return mixed
     */
    function setInitialState();

    /**
     * execute the workflow
     * @return mixed
     */
    function run();

    /**
     * Return the current status of the workflow
     * @return \Mothership\StateMachine\StatusInterface $status
     */
    function getCurrentStatus();

    /**
     * Set the status of the workflow
     * @param \Mothership\StateMachine\StatusInterface $status
     * @return mixed
     */
    function setState(StatusInterface $status);

    /**
     * Get the status of the workflow by its name
     * @param $name
     * @return mixed
     */
    function getStatus($name);
}
