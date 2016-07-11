<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

/**
 * Interface StatusInterface.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
interface StatusInterface
{
    // There can only be on state 'initial'
    const TYPE_INITIAL = 'initial';

    // There also can only be one state final
    const TYPE_FINAL = 'final';

    // All other states must be normal
    const TYPE_NORMAL = 'normal';

    // only for exception states
    const TYPE_EXCEPTION = 'exception';

    /**
     * Returns the state name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the state type.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the available transitions.
     *
     * @return array
     */
    public function getTransitions();

    /**
     * Returns optional state properties.
     *
     * @return mixed
     */
    public function getProperties();
}
