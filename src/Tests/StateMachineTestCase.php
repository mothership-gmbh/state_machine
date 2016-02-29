<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mothership\StateMachine\Tests;

/**
 * Class Mothership\StateMachine\Tests\StateMachineTestCase
 *
 * @category  Mothership
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

    /**
     * Get the current working directory
     *
     * @return string
     */
    protected function getDir()
    {
        return getcwd();
    }
}