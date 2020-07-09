<?php

namespace tests\testClasses;

use Di\DIGlobalProvider;
use tests\testClasses\nested\level1a;

class GlobalInstanceProvider extends DIGlobalProvider
{
    public function getGlobalInstance()
    {
        return $this->getGlobalInstanceOf(level1a::class);
    }
}
