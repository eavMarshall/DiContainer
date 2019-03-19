<?php

namespace tests\testClasses\nested;

class level1a
{
    public $level2a;
    public $level2b;

    public function __construct(level2a $level2a, level2b $level2b)
    {
        $this->level2a = $level2a;
        $this->level2b = $level2b;
    }
}