<?php

namespace tests\testClasses\nested;

class top
{
    public $level1a;
    public $level1b;

    public function __construct(level1a $level1a, level1b $level1b)
    {
        $this->level1a = $level1a;
        $this->level1b = $level1b;
    }
}