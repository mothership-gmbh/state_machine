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
 * @package   Mothership_{EXTENSION NAME}
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright Copyright (c) 2015 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */

namespace Exemple\IfConditions;

use Mothership\StateMachine\WorkflowAbstract;

class IfConditionsWorkflow extends WorkflowAbstract
{
    protected $index = 0;
    protected $limit = 10;

    function start()
    {

    }

    function second_state()
    {

    }

    function third_state()
    {
        if ($this->index > $this->limit) {
            return 0;
        } else {
            return rand(1, 2);
        }
    }

    function transition_state()
    {
        $this->index++;
    }

    function second_transition_state()
    {
        $this->index++;
    }

    function finish()
    {

    }
}
