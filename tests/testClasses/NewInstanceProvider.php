<?php

namespace tests\testClasses;

use Di\DIProvider;
use tests\testClasses\nested\level1a;

class NewInstanceProvider extends DIProvider
{
    public function getNewInstance()
    {
        return $this->getInstanceOf(level1a::class);
    }
}
