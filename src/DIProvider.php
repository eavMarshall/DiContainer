<?php

namespace Di;

/**
 * @author     Elliott Marshall
 * @copyright  2022
 * @license    MIT
 */
abstract class DIProvider implements SingleInstance
{
    public function __construct(private DIContainer $container) {}

    protected function getInstanceOf($class)
    {
        return $this->container->getInstanceOf($class);
    }

    abstract public function getNewInstance();
}
