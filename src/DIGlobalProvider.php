<?php

namespace Di;

abstract class DIGlobalProvider implements SingleInstance
{
    private $container;

    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    protected function getDiContainer(): DIContainer
    {
        return $this->container;
    }

    protected function getGlobalInstanceOf($class)
    {
        return $this->getDiContainer()
            ->getInstanceOf(GlobalInstances::class)
            ->getGlobalInstanceOf($class);
    }
    abstract public function getGlobalInstance(

    );
}
