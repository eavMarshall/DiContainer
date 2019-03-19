<?php
namespace Di;

/**
 * Interface SingleInstance
 * Any class implementing this will be tag as a singleton by DIContainer. The on first instance the object of type
 * class will be cached and when called from DIContainer that same instance will be returned
 */
interface SingleInstance
{

}