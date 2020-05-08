[![Build Status](https://img.shields.io/travis/eavMarshall/DiContainer/master.svg?style=flat-square)](https://travis-ci.org/eavMarshall/DiContainer)
[![Coverage Status](https://coveralls.io/repos/github/eavMarshall/DiContainer/badge.svg?branch=master)](https://coveralls.io/github/eavMarshall/DiContainer?branch=master)

# DiContainer
DiContainer is a lightweight dependency injection container for PHP 5.6.9 - 7.4

#### How to start [here](https://github.com/eavMarshall/DiContainer/wiki/How-to-start)
```php
$diContainer = new DIContainer();
$myClass = $diContainer->getInstanceOf(MyClass::class);
```
The function getInstanceOf will return a instance of MyClass with all it's dependencies and their dependencies injected into the constructor.

#### This container is designed to be
- [Immutable](https://github.com/eavMarshall/DiContainer/wiki/Immutable)
- [Rule free](https://github.com/eavMarshall/DiContainer/wiki/Rule-free)
- [Work immediately with no setup](https://github.com/eavMarshall/DiContainer/wiki/Work-immediately-with-no-setup%3F)
- [Help remove the singleton pattern](https://github.com/eavMarshall/DiContainer/wiki/Help-remove-the-singleton-pattern)
- [Testing friendly, easily swapping instances for mock/stubs](https://github.com/eavMarshall/DiContainer/wiki/Testing-friendly,-easily-swapping-instances-for-mock-stubs)
- [Performances tests for php 5.6](https://github.com/eavMarshall/DiContainer/wiki/Performances-tests-for-php-5.6)
- [Performance tests for php 7.3](https://github.com/eavMarshall/DiContainer/wiki/Performance-tests-for-php-7.3)

#### Creating providers to pass the same instance everywhere
When implementing SingleInstance the container will pass back the same service everywhere. No config needed.
```php
class MyClass implements SingleInstance {
  public function getMyService()
  {
    return 'Hello world';
  }
}

trait DiContainerGetterTrait
{
    /**
     * @return DIContainer
     */
    protected function getDiContainer()
    {
        return DIContainer::getInstance();
    }
}

trait GlobalMyClassTrait
{
    /**
     * @return MyClass
     */
    protected function getGlobalMyClass()
    {
        return $this->getDiContainer()->getInstanceOf(MyClass::class);
    }

    /**
     * @return DIContainer
     */
    abstract protected function getDiContainer();
}

class MyOtherClass()
{
  use DiContainerGetterTrait;
  use GlobalMyClassTrait;
  
  public function myOtherMethod()
  {
    return $this-getGlobalMyClass()->getMyService();
  }
}

$diContainer = new DIContainer();
$myOtherClass = $diContainer->getInstanceOf(MyOtherClass::class);
$myOtherClass->myOtherMethod(); // returns Hello world
```

#### Easy to test
Using the addOverrideRule you can replace every call to MyClass instance everywhere
```php
class test_getMyService()
{
  public function test()
  {
    $myClassMock = $this->getMockBuilder(MyClass::class)
        ->setMethods(['getMyService'])
        ->getMock();
    $myClassMock->method('getMyService')->willReturn('Hello world v2');
    
    //Notice here, the addOverrideRule function return a new container
    $testContainer = (new DIContainer())->addOverrideRule(MyClass::class, function () use (&$myClassMock) {
        return $myClassMock;
    });

    $myOtherClass = $testContainer->getInstanceOf(MyOtherClass::class);
    $myOtherClass->myOtherMethod(); // returns Hello world v2
  }
}
```

