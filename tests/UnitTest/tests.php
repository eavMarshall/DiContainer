<?php

use Di\DIContainer;
use Di\GlobalInstances;
use PHPUnit\Framework\TestCase;
use tests\testClasses\ClassHoldingSessionInfoIsUpdated;
use tests\testClasses\ClassSingleInstance;
use tests\testClasses\ClassThatNeedsClassWithConstructorDependencies;
use tests\testClasses\ClassWithDiContainerDependency;
use tests\testClasses\ClassWithoutDependencies;
use tests\testClasses\GlobalInstanceProvider;
use tests\testClasses\nested\top;
use tests\testClasses\NewInstanceProvider;
use tests\testClasses\PHP8ConstructorPropertyPromotion;
use tests\testClasses\SessionInfo;

final class tests extends TestCase
{
    /** @var DIContainer */
    private $dic;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
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

    public function testApplyingRuleReturnsNewObject()
    {
        $ruleDic = $this->dic->addOverrideRule(Exception::class, static function () {
            return new InvalidArgumentException();
        });

        self::assertNotSame($this->dic, $ruleDic);
        $nonRuleType = $this->dic->getInstanceOf(Exception::class);
        $ruleType = $ruleDic->getInstanceOf(Exception::class);

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
        $this->expectExceptionMessage('Class "This_is_a_class_that_does_not_exist" does not exist');
        $this->dic->getInstanceOf('This_is_a_class_that_does_not_exist');
    }

    public function testGetInstanceReturnsTheSameDIContainer()
    {
        $firstCall = DIContainer::getInstance();
        self::assertInstanceOf(DIContainer::class, $firstCall);

        self::assertSame($firstCall, DIContainer::getInstance());
        self::assertNotSame($this->dic, DIContainer::getInstance());
    }

    public function testCanNotOverrideSelf()
    {
        $container = new DIContainer();
        $overrideContainer = $container->addOverrideRule(DIContainer::class, static function() { return new DIContainer(); });

        self::assertSame($overrideContainer, $overrideContainer->getInstanceOf(DIContainer::class));
    }

    public function saveInstanceValidationProvider()
    {
        return [
            'error: Class can not be null' => [null, null, InvalidArgumentException::class, 'Class can not be falsy'],
            'error: class not exist' => ['some class', null, ReflectionException::class, 'Class "some class" does not exist'],
            'error: class not exists with different param' => ['some class', new stdClass(), ReflectionException::class, 'Class "some class" does not exist'],
        ];
    }

    /**
     * @dataProvider saveInstanceValidationProvider
     */
    public function testSaveInstanceValidation($class, $instances, $errorType, $errorMessage)
    {
        $container = new DIContainer();
        self::expectException($errorType);
        self::expectExceptionMessage($errorMessage);
        $container->getInstanceOf(GlobalInstances::class)->getGlobalInstanceOf($class, $instances);
    }

    public function testSameInstanceReturns()
    {
        $container = new DIContainer();
        $globalInstances = $container->getInstanceOf(GlobalInstances::class);
        $stdClass = new stdClass();
        $classFromGlobal = $globalInstances->getGlobalInstanceOf(stdClass::class);

        self::assertNotSame($stdClass, $classFromGlobal);
        self::assertSame($classFromGlobal, $globalInstances->getGlobalInstanceOf(stdClass::class));
        self::assertNotSame($stdClass, $globalInstances->getGlobalInstanceOf(stdClass::class));

        self::assertNotSame(
            $stdClass,
            (new DIContainer())->getInstanceOf(GlobalInstances::class)->getGlobalInstanceOf(stdClass::class),
            'Containers should have their own "global" scope'
        );

        self::assertNotSame(
            $container->getInstanceOf(stdClass::class),
            (new DIContainer())->getInstanceOf(GlobalInstances::class)->getGlobalInstanceOf(stdClass::class),
            'Container should still be able to create new instances'
        );
    }

    public function testNewInstanceProviders()
    {
        $container = new DIContainer();
        $newInstanceProvider = $container->getInstanceOf(NewInstanceProvider::class);

        self::assertNotSame($newInstanceProvider->getNewInstance(), $newInstanceProvider->getNewInstance());
    }

    public function testGlobalInstanceProvider()
    {
        $container = new DIContainer();
        $globalInstanceProvider = $container->getInstanceOf(GlobalInstanceProvider::class);

        self::assertSame($globalInstanceProvider->getGlobalInstance(), $globalInstanceProvider->getGlobalInstance());
    }

    public function testPhp8ConstructorPropertyPromotionClassCanBeCreated()
    {
        $container = new DIContainer();
        $instance = $container->getInstanceOf(PHP8ConstructorPropertyPromotion::class);
        self::assertInstanceOf(PHP8ConstructorPropertyPromotion::class, $instance);
        self::assertInstanceOf(NewInstanceProvider::class, $instance->instanceProvider);
        self::assertInstanceOf(SessionInfo::class, $instance->sessionInfo);
    }
}
