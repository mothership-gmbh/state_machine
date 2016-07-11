<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine\Tests;

use Mothership\StateMachine\StatusInterface;
use Symfony\Component\Yaml\Yaml;


/**
 * Class StateMachineTest.
 *
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
class StateMachineTest extends \Mothership\StateMachine\Tests\StateMachineTestCase
{
    protected $state_machine_dir;

    /**
     * The yaml file.
     *
     * @var array
     */
    protected $yamlfile = [];

    /**
     * Test for rendering the state machine
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_0
     *
     * @dataProvider stateMachineProvider
     */
    public function testRenderGraph($dir, $class, $yml)
    {
        fwrite(STDERR, sprintf("\nCurrent workflow: %s", $class));

        $path = getcwd() . '/' . str_replace("\\", "_", $class) . '.png';

        echo $path;

        /** @var \Mothership\StateMachine\StateMachineAbstract $stateMachine */
        $stateMachine = new $class(Yaml::parse(file_get_contents($yml)));
        $stateMachine->renderGraph($path, false);
        $this->assertTrue(file_exists($path));
    }

    /**
     * Get all state machines
     *
     * @return array
     */
    public function stateMachineProvider()
    {
        $this->state_machine_dir = $this->getExamplesDir();
        $state_machines = [];
        foreach ($this->state_machine_dir as $dir) {
            array_push(
                $state_machines,
                [
                    $dir['PATH'],
                    'Mothership\\StateMachine\\Examples\\'.$dir['NAME'].'\\'.$dir['NAME'].'StateMachine',
                    getcwd().'/src/Examples/'.$dir['NAME'].'/Workflow.yml',
                ]
            );
            array_push($this->yamlfile, getcwd().'/src/Examples/'.$dir['NAME'].'/Workflow.yml');
        }

        return $state_machines;
    }

    /**
     * A workflow defines a set of mandatory methods. If the methods are not present,
     * then throw an exception.
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_0
     *
     * @dataProvider stateMachineProvider
     */
    public function testParseYAML($dir, $class, $yml)
    {
        $state_machine = new $class(Yaml::parse(file_get_contents($yml)));
        $yaml_array = $this->invokeMethod($state_machine, 'parseYAML');
        $this->assertArrayHasKey('states', $yaml_array);
        $this->assertArrayHasKey('class', $yaml_array);
        $this->assertArrayHasKey('args', $yaml_array['class']);
        foreach ($yaml_array['states'] as $state) {
            $this->assertArrayHasKey('name', $state);
            $this->assertArrayHasKey('type', $state);
            if ($state['type'] != 'initial' && $state['type'] != 'exception') {
                $this->assertArrayHasKey('transitions_from', $state);
                $this->assertArrayHasKey('transitions_to', $state);
            }
        }
    }

    /**
     * A workflow defines a set of mandatory methods. If the methods are not present,
     * then throw an exception.
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_0
     *
     * @expectedException        \Mothership\StateMachine\Exception\StateMachineException
     * @expectedExceptionMessage This methods are not implemented in the workflow
     */
    public function testMethodNotImplementedException()
    {
        $workflow = getcwd().'/src/Examples/Fail/Workflow.yml';
        new \Mothership\StateMachine\Examples\Fail\FailStateMachine(Yaml::parse(file_get_contents($workflow)));
    }

    /**
     * The class name of the workflow must be defined. If the class does not exist, then throw an error.
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_1
     *
     * @expectedException        \Mothership\StateMachine\Exception\StateMachineException
     * @expectedExceptionMessage
     */
    public function noWorkflowFilePresent()
    {
        new \Mothership\StateMachine\StateMachine([], new \Symfony\Component\Console\Output\ConsoleOutput());
    }

    /**
     * The class name of the workflow must be defined. If the class does not exist, then throw an error.
     *
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_2
     *
     * @expectedException        \Mothership\StateMachine\Exception\StateMachineException
     * @expectedExceptionMessage The class Idontexist does not exist!
     */
    public function workflowInitializationFailed()
    {
        $invalidWorkflow = $this->getDir().'/src/Tests/Fixtures/Workflows/InvalidWorkflowClass.yml';
        $stateMachine = new \Mothership\StateMachine\StateMachine(Yaml::parse(file_get_contents($invalidWorkflow)), new \Symfony\Component\Console\Output\ConsoleOutput());
        $stateMachine->run();
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_3
     *
     * @dataProvider stateMachineProvider
     */
    public function hinitializationWorks($dir, $class, $yml)
    {
        //fwrite(STDERR, sprintf("\nCurrent workflow: %s", $class));

        $stateMachine = new $class(Yaml::parse(file_get_contents($yml)), new \Symfony\Component\Console\Output\ConsoleOutput());
        $this->assertTrue($stateMachine->getWorkflow() instanceof \Mothership\StateMachine\WorkflowAbstract);
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_4
     *
     * @dataProvider stateMachineProvider
     */
    public function hasWorkflowAfterInitialization($dir, $class, $yml)
    {
        $stateMachine = new \Mothership\StateMachine\StateMachine(Yaml::parse(file_get_contents($yml)), new \Symfony\Component\Console\Output\ConsoleOutput());
        $stateMachine->run();
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_5
     *
     * @dataProvider stateMachineProvider
     */
    public function stateMachineWillReachFinalState($dir, $class, $yml)
    {
        $stateMachine = new \Mothership\StateMachine\StateMachine(Yaml::parse(file_get_contents($yml)), new \Symfony\Component\Console\Output\ConsoleOutput());
        $stateMachine->run();
        $currentState = $stateMachine->getWorkflow()->getCurrentStatus();
        $this->assertTrue($currentState->getType() === StatusInterface::TYPE_FINAL);
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_6
     *
     * @dataProvider stateMachineProvider
     */
    public function logIsEnabledAndReturnsValues($dir, $class, $yml)
    {
        $stateMachine = new \Mothership\StateMachine\StateMachine(Yaml::parse(file_get_contents($yml)), new \Symfony\Component\Console\Output\ConsoleOutput());
        $stateMachine->run([], \Mothership\StateMachine\WorkflowInterface::ENABLE_LOG);
        $log = $stateMachine->getWorkflow()->getLog();
        $this->assertTrue(is_array($log));

        /*
         * By convention, the first state is called 'start' and the last one 'finish'. By
         * checking for this value, you can be sure, that the log contains valid values
         */
        $this->assertEquals('start', $log[0]['name']);
        $this->assertEquals('finish', end($log)['name']);
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_7
     *
     * @dataProvider stateMachineProvider
     */
    public function acceptanceTestWillNotFail($dir, $class, $yml)
    {
        $stateMachine = new \Mothership\StateMachine\StateMachine(Yaml::parse(file_get_contents($yml)), new \Symfony\Component\Console\Output\ConsoleOutput());
        $stateMachine->run([], \Mothership\StateMachine\WorkflowInterface::ENABLE_LOG);
        $log = $stateMachine->getWorkflow()->getLog();

        $stateMachine->getWorkflow()->acceptance($log);
        $stateMachine->acceptance($log); // method equals to the above. Just a wrapper
    }

    /**
     * @test
     *
     * @group Mothership
     * @group Mothership_StateMachine
     * @group Mothership_StateMachine_8
     *
     * @dataProvider stateMachineProvider
     */
    public function injectArguments($dir, $class, $yml)
    {
        $stateMachine = new \Mothership\StateMachine\StateMachine(Yaml::parse(file_get_contents($yml)), new \Symfony\Component\Console\Output\ConsoleOutput());

        // Inject random values
        $maxKeys = rand(5, 10);
        $args = [];
        for ($i = 0; $i <= $maxKeys; ++$i) {
            $args[uniqid($i)] = uniqid();
        }

        $stateMachine->run($args);
        $this->assertEquals($args, $stateMachine->getWorkflow()->getArgs());

        foreach ($args as $key => $value) {
            $this->assertEquals($value, $stateMachine->getWorkflow()->getArgs($key));
        }
    }
}
