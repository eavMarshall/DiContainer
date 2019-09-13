<?php

namespace tests\testClasses;

use Di\DIContainer;
use Di\SingleInstance;

class ClassWithDiContainerDependency
{
    public $DIContainer;

    public function __construct(DIContainer $DIContainer)
    {
        $this->DIContainer = $DIContainer;
    }
}