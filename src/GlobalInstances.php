<?php

namespace Di;

use InvalidArgumentException;

class GlobalInstances implements SingleInstance
{
    private $container;
    private $globalInstances = [];

    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    protected function getDiContainer(): DIContainer
    {
        return $this->container;
    }
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
        if ($class === null) {
            throw new InvalidArgumentException('Class can not be null');
        }

        if (!$this->hasInstance($class)) {
            $this->saveInstance($class, $this->getDiContainer()->getInstanceOf($class));
        }

        return $this->getGlobalInstance($class);
    }
}
