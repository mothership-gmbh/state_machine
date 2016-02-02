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
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright Copyright (c) 2016 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */

namespace Mothership\StateMachine\Tests;

/**
 * StateMachineTestCase
 *
 * @category  Mothership
 * @package   Mothership_StateMachine
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 * @link      http://www.mothership.de/
 */
class StateMachineTestCase extends \PHPUnit_Framework_TestCase
{
    // should be \Mothership\Base\Tests\Trait
    use \Mothership\Tests\TraitBase;

    /**
     * The directory with all examples
     *
     * @var string
     */
    protected $exampleDir = '/src/Examples';

    protected $excludeDir = ['Fail'];

    /**
     * Return all the directories containing examples
     *
     * @return array
     */
    protected function getExamplesDir()
    {
        $objects = scandir(getcwd() . $this->exampleDir);
        $dir = [];
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..' && is_dir(getcwd() . '/' . $this->exampleDir . '/' . $object)) {
                foreach($this->excludeDir as $exclude)  {
                    if($exclude!=$object)   {
                        array_push($dir, ['PATH' => getcwd() . '/' . $this->exampleDir . '/' . $object . '/', 'NAME' => $object]);
                    }
                }
            }
        }
        return $dir;
    }
}