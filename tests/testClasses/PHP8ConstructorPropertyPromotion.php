<?php

namespace tests\testClasses;

class PHP8ConstructorPropertyPromotion
{
    public function __construct(
        public NewInstanceProvider $instanceProvider,
        public SessionInfo $sessionInfo,
    ) { }
}
