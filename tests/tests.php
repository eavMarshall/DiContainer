<?php

use Di\DIContainer;
use Di\SessionInfo;
use tests\testClasses\ClassHoldingSessionInfoIsUpdated;
use tests\testClasses\ClassNewAndSingleInstance;
use tests\testClasses\ClassSingleInstance;
use tests\testClasses\ClassThatNeedsClassWithConstructorDependencies;
use tests\testClasses\ClassWithDiContainerDependency;
use tests\testClasses\ClassWithoutDependencies;
use tests\testClasses\nested\top;

final class tests extends PHPUnit_Framework_TestCase
{
    public function testGetSaveInstanceOfReturnsSameInstance()
    {
        $instance1 = DIContainer::loadInstanceOf(ClassSingleInstance::class);
        $instance2 = DIContainer::loadInstanceOf(ClassSingleInstance::class);
        self::assertSame($instance1, $instance2);
    }

    public function testGetInstanceOfReturnsNewInstances()
    {
        $instance1 = DIContainer::loadInstanceOf(ClassWithoutDependencies::class);
        $instance2 = DIContainer::loadInstanceOf(ClassWithoutDependencies::class);
        self::assertNotSame($instance1, $instance2);
    }

    public function testDependancyAreNewInstances()
    {
        $instance1 = DIContainer::loadInstanceOf(ClassThatNeedsClassWithConstructorDependencies::class);
        $instance2 = DIContainer::loadInstanceOf(ClassThatNeedsClassWithConstructorDependencies::class);
        self::assertNotSame($instance1->myDependency, $instance2->myDependency);
        self::assertNotSame($instance1, $instance2);
    }

    public function testDiContainerInsertsItselfAsADependancy()
    {
        $instance1 = DIContainer::loadInstanceOf(ClassWithDiContainerDependency::class);
        self::assertSame(DIContainer::getInstance(), $instance1->DIContainer);
    }

    public function testDiContainerReturnsNewInstanceWhenImplementingNewInstance()
    {
        $instance1 = DIContainer::loadInstanceOf(ClassNewAndSingleInstance::class);
        $instance2 = DIContainer::loadInstanceOf(ClassNewAndSingleInstance::class);
        self::assertNotSame($instance1, $instance2);
    }

    public function testSessionInfoIsUpdatedInInstance()
    {
        $quote = 'get to the choppa';
        $sessionInfo = DIContainer::loadInstanceOf(SessionInfo::class);
        $sessionInfo->sessionField1 = $quote;
        $instance1 = DIContainer::loadInstanceOf(ClassHoldingSessionInfoIsUpdated::class);
        self::assertEquals($quote, $instance1->sessionInfo->sessionField1);

        $instance2 = DIContainer::loadInstanceOf(ClassHoldingSessionInfoIsUpdated::class);
        self::assertEquals($quote, $instance2->sessionInfo->sessionField1);

        $quote = "I don't have time to bleed";
        $sessionInfo->sessionField1 = $quote;
        self::assertEquals($quote, $instance1->sessionInfo->sessionField1);
        self::assertEquals($quote, $instance2->sessionInfo->sessionField1);

        $quote = "There's something out there waiting for us, and it ain't no man";
        DIContainer::loadInstanceOf(SessionInfo::class)->sessionField1 = $quote;
        self::assertEquals($quote, $instance1->sessionInfo->sessionField1);
        self::assertEquals($quote, $instance2->sessionInfo->sessionField1);
    }

    public function nestedTest()
    {
        $instance1 = DIContainer::loadInstanceOf(top::class);
        $instance2 = DIContainer::loadInstanceOf(top::class);
        self::assertNotSame($instance1, $instance2);
        self::assertTrue(isset($instance1->level1a));
        self::assertTrue(isset($instance1->level1a->level2a));
        self::assertTrue(isset($instance1->level1a->level2b));
        self::assertTrue(isset($instance1->level1b));
        self::assertTrue(isset($instance1->level1b->level2c));
        self::assertTrue(isset($instance1->level1b->level2d));
        self::assertFalse(isset($instance1->level1b->level2d->nothingHere));
    }
}