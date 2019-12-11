[![Build Status](https://img.shields.io/travis/eavMarshall/DiContainer/master.svg?style=flat-square)](https://travis-ci.org/eavMarshall/DiContainer)
[![Coverage Status](https://coveralls.io/repos/github/eavMarshall/DiContainer/badge.svg?branch=master)](https://coveralls.io/github/eavMarshall/DiContainer?branch=master)

# DiContainer
DiContainer is a lightweight dependency injection container for PHP 5.6.9, 7.1 - 7.4

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
