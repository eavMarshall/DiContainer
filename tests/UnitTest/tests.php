<?php

use Di\DIContainer;
use PHPUnit\Framework\TestCase;
use tests\testClasses\ClassHoldingSessionInfoIsUpdated;
use tests\testClasses\ClassSingleInstance;
use tests\testClasses\ClassThatNeedsClassWithConstructorDependencies;
use tests\testClasses\ClassWithDiContainerDependency;
use tests\testClasses\ClassWithoutDependencies;
use tests\testClasses\nested\top;
use tests\testClasses\SessionInfo;

final class tests extends TestCase
{
    /** @var DIContainer */
    private $dic;

    protected function setUp(): void
    {
        $this->dic = new DIContainer();
    }

    public function testGetSaveInstanceOfReturnsSameInstance()
    {
        $instance1 = $this->dic->getInstanceOf(ClassSingleInstance::class);
        $instance2 = $this->dic->getInstanceOf(ClassSingleInstance::class);
        self::assertSame($instance1, $instance2);
    }

    public function testGetInstanceOfReturnsNewInstances()
    {
        $instance1 = $this->dic->getInstanceOf(ClassWithoutDependencies::class);
        $instance2 = $this->dic->getInstanceOf(ClassWithoutDependencies::class);
        self::assertNotSame($instance1, $instance2);
    }

    public function testDependancyAreNewInstances()
    {
        $instance1 = $this->dic->getInstanceOf(ClassThatNeedsClassWithConstructorDependencies::class);
        $instance2 = $this->dic->getInstanceOf(ClassThatNeedsClassWithConstructorDependencies::class);
        self::assertNotSame($instance1->myDependency, $instance2->myDependency);
        self::assertNotSame($instance1, $instance2);
    }

    public function testDiContainerInsertsItselfAsADependancy()
    {
        $instance1 = $this->dic->getInstanceOf(ClassWithDiContainerDependency::class);
        self::assertSame($this->dic, $instance1->DIContainer);
    }

    public function testSessionInfoIsUpdatedInInstance()
    {
        $quote = 'get to the choppa';
        $sessionInfo = $this->dic->getInstanceOf(SessionInfo::class);
        $sessionInfo->sessionField1 = $quote;
        $instance1 = $this->dic->getInstanceOf(ClassHoldingSessionInfoIsUpdated::class);
        self::assertEquals($quote, $instance1->sessionInfo->sessionField1);

        $instance2 = $this->dic->getInstanceOf(ClassHoldingSessionInfoIsUpdated::class);
        self::assertEquals($quote, $instance2->sessionInfo->sessionField1);

        $quote = "I don't have time to bleed";
        $sessionInfo->sessionField1 = $quote;
        self::assertEquals($quote, $instance1->sessionInfo->sessionField1);
        self::assertEquals($quote, $instance2->sessionInfo->sessionField1);

        $quote = "There's something out there waiting for us, and it ain't no man";
        $this->dic->getInstanceOf(SessionInfo::class)->sessionField1 = $quote;
        self::assertEquals($quote, $instance1->sessionInfo->sessionField1);
        self::assertEquals($quote, $instance2->sessionInfo->sessionField1);
    }

    public function nestedTest()
    {
        $instance1 = $this->dic->getInstanceOf(top::class);
        $instance2 = $this->dic->getInstanceOf(top::class);
        self::assertNotSame($instance1, $instance2);
        self::assertTrue(isset($instance1->level1a));
        self::assertTrue(isset($instance1->level1a->level2a));
        self::assertTrue(isset($instance1->level1a->level2b));
        self::assertTrue(isset($instance1->level1b));
        self::assertTrue(isset($instance1->level1b->level2c));
        self::assertTrue(isset($instance1->level1b->level2d));
        self::assertFalse(isset($instance1->level1b->level2d->nothingHere));
    }

    public function testNewInstanceWithParams()
    {
        $message = 'this is a message from my new exception';
        $exception = $this->dic->getInstanceOf(Exception::class, [$message]);

        self::assertEquals($message, $exception->getMessage());
    }

    public function testApplyingRuleReturnsNewObject()
    {
        $ruleDic = $this->dic->addOverrideRule(Exception::class, static function () {
            return new InvalidArgumentException();
        });

        self::assertNotSame($this->dic, $ruleDic);
        $nonRuleType = $this->dic->getInstanceOf(Exception::class, ['test']);
        $ruleType = $ruleDic->getInstanceOf(Exception::class, ['test']);

        self::assertInstanceOf(Exception::class, $nonRuleType);
        self::assertInstanceOf(InvalidArgumentException::class, $ruleType);
    }

    public function testUnknownTypeGetsPassedANull()
    {
        $exception = $this->dic->getInstanceOf(Exception::class);
        self::assertInstanceOf(Exception::class, $exception);
    }

    public function testClassNotExist()
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class This_is_a_class_that_does_not_exist does not exist');
        $this->dic->getInstanceOf('This_is_a_class_that_does_not_exist');
    }
}