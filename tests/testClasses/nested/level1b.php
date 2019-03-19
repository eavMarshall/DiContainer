<?php

namespace tests\testClasses\nested;

class level1b
{
    public $level2c;
    public $level2d;

    public function __construct(level2c $level2c, level2d $level2d)
    {
        $this->level2c = $level2c;
        $this->level2d = $level2d;
    }
}