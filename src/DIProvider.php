<?php

namespace Di;

abstract class DIProvider implements SingleInstance
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

    abstract public function getNewInstance();
}
