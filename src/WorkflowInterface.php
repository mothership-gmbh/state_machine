<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

/**
 * Class WorkflowInterface provide an interface for WorkflowObjects.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
interface WorkflowInterface
{
    /**
     * By setting this value, you will be able to save the log somewhiere.
     */
    const ENABLE_LOG = true;

    /**
     * Set the inizial state of the workflow.
     *
     * @return mixed
     */
    public function setInitialState();

    /**
     * execute the workflow.
     *
     * @return mixed
     */
    public function run();

    /**
     * Return the current status of the workflow.
     *
     * @return \Mothership\StateMachine\StatusInterface $status
     */
    public function getCurrentStatus();

    /**
     * Set the status of the workflow.
     *
     * @param \Mothership\StateMachine\StatusInterface $status
     *
     * @return mixed
     */
    public function setState(StatusInterface $status);

    /**
     * Get the status of the workflow by its name.
     *
     * @param $name
     *
     * @return mixed
     */
    public function getStatus($name);
}
