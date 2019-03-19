<?php

namespace tests\testClasses;

use Di\SessionInfo;

class ClassHoldingSessionInfoIsUpdated
{
    public $sessionInfo;

    public function __construct(SessionInfo $sessionInfo)
    {
        $this->sessionInfo = $sessionInfo;
    }
}