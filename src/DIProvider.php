<?php

namespace Di;

/**
 * @author     Elliott Marshall
 * @copyright  2022
 * @license    MIT
 */
abstract class DIProvider implements SingleInstance
{
    private $container;

    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    protected function getInstanceOf($class)
    {
        return $this->container->getInstanceOf($class);
    }

    abstract public function getNewInstance();
}
