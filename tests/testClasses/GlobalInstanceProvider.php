<?php

namespace tests\testClasses;

use Di\DIGlobalProvider;
use Di\GlobalInstances;
use tests\testClasses\nested\level1a;

class GlobalInstanceProvider extends DIGlobalProvider
{
    public function getGlobalInstance()
    {
        return $this->getDiContainer()
            ->getInstanceOf(GlobalInstances::class)
            ->getGlobalInstanceOf(level1a::class);
    }
}
