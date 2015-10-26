Mothership\StateMachine ![](https://travis-ci.org/mothership-gmbh/state_machine.svg?branch=master)
-----------------------------------------
State Machine pattern

#Features
- Create, configure and run a complete State Machine.
- Configuration of each state of the machine can be set completely up from a *yml* file.
- Possibility to run State machine with only one command that visits all the possible nodes
- Possibility to render an graphic image that show the behaviour of the state machine
    
  ![](https://github.com/mothership-gmbh/state_machine/blob/develop/exemple/IfConditions/workflow.png)

#HOW
In the folder *exemple* there are some exemple of how use this repo.

All exemple are tested and run: they are the base for all the unit test.

##Your State machine
- create your *yml* configuration file where you define:
```
class:
  name: Exemple\SimpleStateMachine\Workflow
  args: []
```
*class*: is the class containing the implementation methods for the state machine

*args*: array of arguments for the constructor class of the state machine

```
states:
  start:
    type: initial
  second_state:
    type: normal
    transitions_from: [start]
    transitions_to: [second_state]
  ...
  finish:
    type: final
    transitions_from:  [third_state]
    transitions_to:  [finish]
```
All the states and the transiction from one state to another:

**type**: type of the state that can be *initial*,*normal* or *final*.

**transition_from**: the states from which the current state can start.

**transition_to**: the state in which the machine will arrive after the execution of the state (Usually is the same state)

###Use conditional transitions
To use a condition inside a transiction add inside the *yml* configuration something like the [*ifConditions* 
machine].(https://github.com/mothership-gmbh/state_machine/blob/develop/exemple/IfConditions/workflow.yml)

```
transitions_from:
      - {status:  third_state, result:  1}
```

- Create your php class *Workfow* for the state machine (The same configured in the *yml* configuration file) that extends **Mothership\StateMachine\WorkflowAbstract**
```
namespace Exemple\SimpleStateMachine;

use Mothership\StateMachine\WorkflowAbstract;

class Workflow extends WorkflowAbstract
{
    function start()
    {

    }

    function second_state()
    {

    }

    function third_state()
    {

    }

    function finish()
    {

    }
}
```

- Create your own State machine extending **Mothership\StateMachine\StateMachineAbstract**
```
namespace Exemple\SimpleStateMachine;

use \Exemple\SimpleStateMachine\SimpleStateMachineWorkflow;

use Mothership\StateMachine\StateMachineAbstract;

class StateMachine extends StateMachineAbstract
{

}
```

- Run your state machine:
```
<?php
    $state_machine = new StateMachine();
    $state_machine = $state_machine->run();
```
##Render the graph
Render the graph of your state machine:

```
<?php
    $state_machine = new StateMachine();
    $state_machine = $state_machine->renderGraph($path, false);
```

**$paht**: path where the state machine will save the image

**true/false**: if you want that after the render the state machine exits (default is true)

#Installation
StateMachine need *graphviz* library installed on the machine to render the graph option:
```
sudo apt-get install graphviz
```

#Tests
- Run test from root directory:
```
phpunit --coverage-text
```

#Notes
- **Mothership StateMachine** is inspired by [Finite/StateMachine](https://github.com/yohang/Finite) presents in this extension
