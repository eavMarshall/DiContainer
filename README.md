[![Build Status](https://img.shields.io/travis/eavMarshall/DiContainer/master.svg?style=flat-square)](https://travis-ci.org/eavMarshall/DiContainer)
[![Coverage Status](https://coveralls.io/repos/github/eavMarshall/DiContainer/badge.svg?branch=master)](https://coveralls.io/github/eavMarshall/DiContainer?branch=master)

# DiContainer
DiContainer is a lightweight dependency injection container for PHP
- (v1.01 - PHP 5.6)
- (v1.03 - PHP 7)
- (v1.04 - PHP 8, not yet released)

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
- [Performance tests for php 8](https://github.com/eavMarshall/DiContainer/wiki/Performance-test-for-php-8)

#### Creating providers to pass the same instance everywhere
When implementing SingleInstance the container will pass back the same service everywhere. No config needed.
```php
class MyClass implements SingleInstance {
  public function getMyService()
  {
    return 'Hello world';
  }
}

$diContainer = new DIContainer();
$myClass1 = $diContainer->getInstanceOf(MyClass::class);
$myClass2 = $diContainer->getInstanceOf(MyClass::class);
// myClass1 & myClass2 are the same instance
```

If you need a global class and a single instance of the same class
```php
class MyClass {
  public function getMyService()
  {
    return 'Hello world';
  }
}

class GlobalMyClass {
    public function __construct(private MyClass $myClass) {}
    public MyClass function getGlobalInstance() {
        return $this->myClass;
    }
}

$diContainer = new DIContainer();
$myClass1 = $diContainer->getInstanceOf(MyClass::class);
$myClass2 = $diContainer->getInstanceOf(MyClass::class);
// myClass1 & myClass2 are different instances

$myClass3 = $diContainer->getInstanceOf(GlobalMyClass::class)->getGlobalInstance();
$myClass4 = $diContainer->getInstanceOf(GlobalMyClass::class)->getGlobalInstance();
// myClass3 & myClass4 are the same instance
// myClass3 & 4 are different instances for class1 & 2
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
    
    //addOverrideRule function return a new container with the new rules
    $testContainer = (new DIContainer())->addOverrideRule(MyClass::class, function () use (&$myClassMock) {
        return $myClassMock;
    });

    $myOtherClass = $testContainer->getInstanceOf(MyOtherClass::class);
    $myOtherClass->myOtherMethod(); // returns Hello world v2
  }
}
```

