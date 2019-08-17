<?php

namespace tests\testClasses;

class ClassHoldingSessionInfoIsUpdated
{
    public $sessionInfo;

    public function __construct(SessionInfo $sessionInfo)
    {
        $this->sessionInfo = $sessionInfo;
    }
}