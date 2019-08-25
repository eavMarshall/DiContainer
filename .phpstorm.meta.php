<?php
namespace PHPSTORM_META {

    use Di\DIContainer;

    override(DIContainer::getInstanceOf(),
        map([
            "" == "@"
        ]));
}