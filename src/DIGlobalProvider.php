<?php

namespace Di;

/**
 * @author     Elliott Marshall
 * @copyright  2022
 * @license    MIT
 */
abstract class DIGlobalProvider implements SingleInstance
{
    public function __construct(private DIContainer $container) {}

    protected function getGlobalInstanceOf($class)
    {
        return $this->container
            ->getInstanceOf(GlobalInstances::class)
            ->getGlobalInstanceOf($class);
    }
    abstract public function getGlobalInstance();
}
