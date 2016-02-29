<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine\Tests;

use Mothership\StateMachine\StatusInterface;
use \Mothership\StateMachine\Tests\StateMachineTestCase;

/**
 * Class StatusTest.
 *
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
class StatusTest extends StateMachineTestCase
{
    /**
     * @var  \Mothership\StateMachine\WorkflowInterface
     */
    protected $workflow;

    /**
     * Initialize the workflow
     */
    public function setUp()
    {
        // Create a stub for the SomeClass class.
        $this->workflow = $this->getMockBuilder('\Mothership\StateMachine\Tests\Fixtures\Workflow')
                               ->disableOriginalConstructor()
                               ->getMock();
    }

    /**
     * The initialization will fail if mandatory parameters are missing
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_1
     *
     * @expectedException \Mothership\StateMachine\Exception\StatusException
     */
    public function initializeWillThrowException()
    {
        new \Mothership\StateMachine\Status([]);
    }

    /**
     * The initialization will fail if mandatory parameters are missing
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_1
     *
     * @expectedException \Mothership\StateMachine\Exception\StatusException
     */
    public function initializeWithMissingKeysThrowException()
    {
        $params = [
            'name' => uniqid(),
            'type' => StatusInterface::TYPE_NORMAL,
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * The initialization will fail if mandatory parameters are missing
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_2
     *
     * @expectedException \Mothership\StateMachine\Exception\StatusException
     * @expectedExceptionMessage The type specified is invalid
     */
    public function initializeWithInvalidTypeWillThrowException()
    {

        $params = [
            'name' => uniqid(),
            'type' => 'invalid',
            'transitions_from' => 'invalid',
            'transitions_to'   => 'invalid',
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * The initialization will fail if mandatory parameters are missing or invalid
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_3
     *
     * @expectedException \Mothership\StateMachine\Exception\StatusException
     * @expectedExceptionMessage Key transitions_from is not an array
     */
    public function initializeWithInvalidTransitionTypeFromWillThrowException()
    {

        $params = [
            'name' => uniqid(),
            'type' => StatusInterface::TYPE_NORMAL,
            'transitions_from' => 'invalid',
            'transitions_to'   => 'invalid',
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * The initialization will fail if mandatory parameters are missing or invalid
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_4
     *
     * @expectedException \Mothership\StateMachine\Exception\StatusException
     * @expectedExceptionMessage Key transitions_to is not an array
     */
    public function initializeWithInvalidTransitionTypeToWillThrowException()
    {

        $params = [
            'name' => uniqid(),
            'type' => StatusInterface::TYPE_NORMAL,
            'transitions_from' => [],
            'transitions_to'   => 'invalid',
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * The initialization will still fail because the transitions are empty
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_5
     *
     * @expectedException \Mothership\StateMachine\Exception\StatusException
     * @expectedExceptionMessage No transitions available
     */
    public function initializeValidWithEmptyTransitionsWillFail()
    {
        $params = [
            'name'             => uniqid(),
            'type'             => StatusInterface::TYPE_NORMAL,
            'transitions_from' => [],
            'transitions_to'   => [],
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * The initialization will succeed
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_6
     */
    public function initializeValidWithEmptyTransitionsWillSucceed()
    {
        $params = [
            'name'             => uniqid(),
            'type'             => StatusInterface::TYPE_NORMAL,
            'transitions_from' => ['anyFrom'],
            'transitions_to'   => ['anyTo'],
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * The init type does not require a transition from
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_7
     */
    public function initializeValidWithEmptyTransitionsWillNotFailForInitializeType()
    {
        $params = [
            'name'             => uniqid(),
            'type'             => StatusInterface::TYPE_INITIAL,
            'transitions_from' => [],
            'transitions_to'   => [],
        ];
        new \Mothership\StateMachine\Status($params);
    }

    /**
     * Get Properties
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_Status
     * @group Mothership_StateMachine_Status_8
     */
    public function properties()
    {
        $params = [
            'name'             => uniqid(),
            'type'             => StatusInterface::TYPE_NORMAL,
            'transitions_from' => ['anyFrom'],
            'transitions_to'   => ['anyTo'],
        ];
        $status = new \Mothership\StateMachine\Status($params);

        $this->assertEquals($params, $status->getProperties());
        $this->assertEquals($params['name'], $status->getName());
        $this->assertEquals($params['type'], $status->getType());
    }
}