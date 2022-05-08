<?php

namespace Di;

use InvalidArgumentException;

/**
 * @author     Elliott Marshall
 * @copyright  2022
 * @license    MIT
 */
class GlobalInstances implements SingleInstance
{
    private $globalInstances = [];

    public function __construct(private DIContainer $container) {}

    private function getGlobalInstance($class)
    {
        return $this->globalInstances[$class];
    }

    private function hasInstance($class)
    {
        return isset($this->globalInstances[$class]);
    }

    private function saveInstance($class, $instance)
    {
        $this->globalInstances[$class] = $instance;
    }

    public function getGlobalInstanceOf($class)
    {
        if (!$class) {
            throw new InvalidArgumentException('Class can not be falsy');
        }

        if (!$this->hasInstance($class)) {
            $this->saveInstance($class, $this->container->getInstanceOf($class));
        }

        return $this->getGlobalInstance($class);
    }
}
