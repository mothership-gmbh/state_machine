<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

/**
 * Class Mothership\StateMachine\CollectionWorkflowAbstract.
 *
 * @category  Mothership
 *
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 * 
 * @link      http://www.mothership.de/
 *
 * @deprecated 
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
     * Build the collection with a numeric index.
     */
    abstract public function prepare_collection();

    /**
     * Process the current collection item.
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
        ++$this->_pointer;

        return true;
    }

    abstract protected function finish();
}
