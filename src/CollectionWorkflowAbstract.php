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
 * @package   Mothership_StateMachine
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright Copyright (c) 2016 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */

/**
 * Class CollectionWorkflowAbstract
 *
 * @category  Mothership
 * @package   Mothership_StateMachine
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 * @link      http://www.mothership.de/
 *
 *            Implements an interface for collections which supports the following transitions
 *
 *            prepare_collection:
 *              type: normal
 *              transitions_from: [<any_state>]
 *              transitions_to:   [prepare_collection]
 *
 *            process_items:
 *              type: normal
 *              transitions_from: [{status:  has_more, result:  true}, prepare_collection]
 *              transitions_to:   [process_item]
 *
 *            has_more:
 *              type: normal
 *              from: [process_item]
 *
 *            finish:
 *              type: final
 *              transitions_from: [{status:  has_more, result:  false}]
 *              transitions_to:   [finish]
 *
 */
abstract class CollectionWorkflowAbstract extends WorkflowAbstract
{
    /**
     * The collection MUST be an array with a numeric Index.
     *
     * eg. [0] => ...
     *     [1] => ...
     *
     * The numeric (i)ndex MUST be i(p=previous_intex) = p+1 for every new entry.
     *
     * @var mixed
     */
    protected $_collection;

    /**
     * The pointer MUST be a numeric value starting with 0.
     * The pointer MUST be incremented in the method has_more if the collection has
     * not been finished yet.
     *
     * @var int
     */
    protected $_pointer = 0;

    /**
     * Build the collection with a numeric index
     *
     * @return void
     */
    abstract public function prepare_collection();

    /**
     * Process the current collection item
     *
     * @return void
     */
    abstract public function process_items();

    /**
     * If the collection has not been finished yet, return false.
     *
     * false -> go to finish()
     * true  -> continue with process_items()
     *
     * @return bool
     */
    public function has_more()
    {
        if ($this->_pointer + 1 == count($this->_collection)) {
            return false;
        }
        $this->_pointer++;

        return true;
    }

    abstract protected function finish();
}