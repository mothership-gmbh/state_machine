<?php
/**
 * This file is part of the Mothership GmbH code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mothership\StateMachine;

use Mothership\StateMachine\Exception\StatusException;
/**
 * Class Status.
 *
 * @category  Mothership
 *
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @author    Don Bosco van Hoi <vanhoi@mothership.de>
 * @copyright 2016 Mothership GmbH
 *
 * @link      http://www.mothership.de/
 */
class Status implements StatusInterface
{
    /**
     * @var array
     */
    protected $properties;

    /**
     * @var array
     */
    protected $types = [StatusInterface::TYPE_INITIAL, StatusInterface::TYPE_NORMAL, StatusInterface::TYPE_FINAL];

    /**
     * @var array|mixed
     */
    protected $transitions = [];

    /**
     * @param array $properties
     *
     * @throws StatusException
     */
    public function __construct(array $properties = [])
    {
        $this->properties = $properties;

        $this->validate();

        $this->transitions = $this->getTransitions();
    }

    /**
     * Validate all required properties on initialization.
     *
     * @throws \Mothership\StateMachine\Exception\StatusException
     */
    private function validate()
    {
        if (!array_key_exists('type', $this->properties)) {
            throw new StatusException(sprintf('Key %s in %s missing', 'type', __CLASS__), 100, null);
        }

        $type = $this->properties['type'];

        // the type specified must be valid
        if (!in_array($this->properties['type'], $this->types)) {
            throw new StatusException('The type specified is invalid', 100, null);
        }

        switch ($type) {
            case StatusInterface::TYPE_INITIAL:
                $mandatoryKeys = ['name'];
                break;

            default:
                $mandatoryKeys = ['name', 'transitions_from', 'transitions_to'];
                break;
        }

        foreach ($mandatoryKeys as $key) {
            if (!array_key_exists($key, $this->properties)) {
                throw new StatusException(sprintf('Key %s in %s missing', $key, __CLASS__), 100, null);
            }
        }

        if (StatusInterface::TYPE_INITIAL != $type) {
            $keysMustBeArray = ['transitions_from', 'transitions_to'];
            foreach ($keysMustBeArray as $key) {
                if (!is_array($this->properties[$key])) {
                    throw new StatusException(sprintf('Key %s is not an array', $key), 100, null);
                }
            }
        }
    }

    /**
     * Get if is an initial state.
     *
     * @return bool
     *
     * @throws StatusException
     */
    protected function isInitialType()
    {
        return $this->getType() == StatusInterface::TYPE_INITIAL;
    }

    /**
     * @return mixed
     *
     * @throws StatusException
     */
    public function getTransitions()
    {
        if (count($this->transitions) == 0 && !$this->isInitialType()) {
            foreach ($this->properties['transitions_from'] as $transition) {
                array_push($this->transitions, new Transition($this, $transition));
            }

            if (count($this->transitions) == 0) {
                throw new StatusException('No transitions available', 100, null);
            }
        } elseif ($this->isInitialType()) {
            $this->transitions = [];
        }

        return $this->transitions;
    }

    /**
     * Returns optional state properties.
     *
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->properties['name'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->properties['type'];
    }
}
