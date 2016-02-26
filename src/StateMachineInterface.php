<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

/**
 * Interface StateMachineInterface.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
interface StateMachineInterface
{
    /**
     * @param array $args
     * @param       $enableLog
     *
     * @return mixed
     * s
     */
    public function run(array $args = [], $enableLog);
}
