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

use Mothership\Tests\MothershipBaseTestCase;

class StateMachineTestCase extends MothershipBaseTestCase
{
    protected $exempleDir = '/exemple';
    protected $excludeDir = ['Fail'];


    /**
     * Return all the directories containing exemples
     * @return array
     */
    protected function getExemplesDir()
    {
        $objects = scandir(getcwd() . $this->exempleDir);
        $dir = [];
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..' && is_dir(getcwd() . '/' . $this->exempleDir . '/' . $object)) {
                foreach($this->excludeDir as $exclude)  {
                    if($exclude!=$object)   {
                        array_push($dir, ['PATH' => getcwd() . '/' . $this->exempleDir . '/' . $object . '/', 'NAME' => $object]);
                    }
                }
            }
        }
        return $dir;
    }

}