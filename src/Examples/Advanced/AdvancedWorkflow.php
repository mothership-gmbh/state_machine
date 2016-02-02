<?php
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
 * @package   Mothership_Aigner
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright Copyright (c) 2016 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */
namespace Mothership\StateMachine\Examples\Advanced;

use Mothership\StateMachine\WorkflowAbstract;

class AdvancedWorkflow extends WorkflowAbstract
{
    protected $_collection = [];
    protected $_pointer    = 0;

    function second_state()
    {

    }

    function load_document()
    {

    }

    /**
     * If there is no image, throw an exception.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function has_images()
    {
    }

    /**
     * If the download directory does not exist, then create it
     *
     * @return void
     */
    public function download_directory_exist()
    {
    }

    /**
     * Every product needs to have a media gallery
     *
     * @return bool
     */
    public function product_has_media_gallery()
    {
        return (rand(0, 1) == 1) ? true: false;
    }

    /**
     * Create the media gallery
     *
     * @return void
     */
    public function create_media_gallery()
    {
    }

    /**
     * Get all images and set the pointer to the first item
     *
     * @return void
     */
    public function get_images()
    {
        //$this->_collection = end($this->_images->parse($this->_document));
        for ($i = 0; $i <= 100; $i++) {
            $this->_collection[] = ['test'];
        }
        $this->_pointer    = 0;
    }

    /**
     * Set the pointer to the current image
     *
     * @return void
     */
    public function process_images()
    {
    }

    /**
     * Check that the current image exist as a copy
     *
     * @return bool
     */
    public function original_image_exist_as_copy()
    {
        return (rand(0, 1) == 1) ? true: false;
    }

    /**
     * The image also needs to have the same checksum and the original one
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function hash_equals_original()
    {
        return (rand(0, 1) == 1) ? true: false;
    }

    /**
     * Remove existing images
     *
     * @return void
     */
    public function remove_existing()
    {
    }

    /**
     * Download from the Intex FTP
     *
     * @return void
     *
     * @throws \Exception
     */
    public function download_original()
    {
    }

    public function assign_image_straight()
    {
    }

    public function assign_image()
    {
    }

    public function has_more()
    {
        if ($this->_pointer +1 == count($this->_collection)) {
            return false;
        }
        $this->_pointer++;
        return true;
    }

    public function finish()
    {

    }
}