Mothership\StateMachine ![](https://travis-ci.org/mothership-gmbh/state_machine.svg?branch=master)
-----------------------------------------
State Machine pattern

#Features
- Create, configure and run a complete State Machine.
- Configuration of each state of the machine can be set completely up from a *yml* file.
- Possibility to run State machine with only one command that visits all the possible nodes
- Possibility to render an graphic image that show the behaviour of the state machine
    
  ![](https://github.com/mothership-gmbh/state_machine/blob/develop/exemple/Simple/workflow.png)

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
- Results of the last master unit tests:
```
Code Coverage Report:      

  2015-10-26 14:57:04      

                           

 Summary:                  

  Classes: 50.00% (6/12)   

  Methods: 32.61% (15/46)  

  Lines:   60.44% (165/273)

\Exemple\Simple::SimpleWorkflow

  Methods:  75.00% ( 3/ 4)   Lines:  75.00% (  3/  4)

\Mothership\Exception::ExceptionAbstract

  Methods:   0.00% ( 0/ 4)   Lines:  45.71% ( 16/ 35)

\Mothership\StateMachine::StateMachineAbstract

  Methods:   0.00% ( 0/ 5)   Lines:  75.41% ( 46/ 61)

\Mothership\StateMachine::Status

  Methods:  38.46% ( 5/13)   Lines:  42.03% ( 29/ 69)

\Mothership\StateMachine::Transition

  Methods:  55.56% ( 5/ 9)   Lines:  68.00% ( 17/ 25)

\Mothership\StateMachine::WorkflowAbstract

  Methods:  18.18% ( 2/11)   Lines:  68.35% ( 54/ 79)
```

#Notes
- **Mothership StateMachine** is inspired by [Finite/StateMachine](https://github.com/yohang/Finite) presents in this extension
