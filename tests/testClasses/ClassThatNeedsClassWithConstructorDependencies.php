<?php

namespace tests\testClasses;

class ClassThatNeedsClassWithConstructorDependencies
{
    public $myDependency = null;

    public function __construct(ClassWithoutDependencies $myDependency)
    {
        $this->myDependency = $myDependency;
    }
}