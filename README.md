[![Build Status](https://img.shields.io/travis/eavMarshall/DiContainer/master.svg?style=flat-square)](https://travis-ci.org/eavMarshall/DiContainer)
[![Coverage Status](https://coveralls.io/repos/github/eavMarshall/DiContainer/badge.svg?branch=master)](https://coveralls.io/github/eavMarshall/DiContainer?branch=master)

# DiContainer
DiContainer is a lightweight dependency injection container for PHP 7

#### How to start
```php
$diContainer = new DIContainer();
$myClass = $diContainer->getInstanceOf(MyClass::class);
```

#### This container is designed to be
- Immutable
- Rule free
- Work immediately with no setup
- Help remove the singleton pattern
- Testing friendly, easily swapping instances for mock/stubs

##### Immutable?
In php you don't have a truly immutable object. The container always holds some state. This state is made of the caches of classes that implements SingleInstance and the overriding rules.
The class definitions can not change while your application is running, and override rules can not be change once the container is instantiated.
So there could be a strong argument that the container is immutable.

##### Rule free?
Every container I've come across implements some sort of rule system. A rule system that needs to be loaded each and every time you run your application.
We can tag shared instances of classes without having to have a rule system by labeling classes with an empty, but known, interface.
See [Help remove the singleton pattern](https://github.com/eavMarshall/DiContainer#help-remove-the-singleton-pattern)

##### Work immediately with no setup?
There are some containers that require complex setup. Usually with json, xml or an array.
If you have an autoloader setup, a fully functioning container can be achieved without any setup.
```php
class DatabaseConnection implements SingleInstance
{
    public function getMyDatabaseConnection()
    {
        return Database::getInstance()->connection('some connection');
    }
}

class MyEndPoint
{
    public __constructor(DatabaseConnection $databaseConnection)
    {
        ...
    }
}

$diContainer = new DIContainer();
$instance1 = $diContainer->getInstanceOf(MyEndPoint::class);
```
The instance of MyEndPoint will have the DatabaseConnection injected into it's constructor automagically

##### Help remove the singleton pattern
This can be achieved by implementing SingleInstance interface to define share instances.
Wrapping the singleton in a class that implements SingleInstance will allow you to request an instance directly from the container, or pass through a constructor of a instance instantiated via the container
In the example above, notice DatabaseConnection implements SingleInstance
```php
$diContainer = new DIContainer();
$instance1 = $diContainer->getInstanceOf(DatabaseConnection::class);
$instance2 = $diContainer->getInstanceOf(DatabaseConnection::class);

assertSame($instance1, $instance2); //passes
```

##### Testing friendly, easily swapping instances for mock/stubs
If we wanted to stub out all DatabaseConnection to return a mock. Adding an override rule will replace all instances of your chosen class
```php
$databaseConnectionMock = $this->getMockBuilder(DatabaseConnection::class)
    ->setMethods([ 'getMyDatabaseConnection' ])
    ->getMock();
$databaseConnectionMock
    ->method('getMyDatabaseConnection')
    ->willReturnCallback(static function () {
        //TODO return my database mock
    });
    
$diContainer = new DIContainer();
$diContainer = $diContainer->addOverrideRule(DatabaseConnection::class, function() use ($databaseConnectionMock) {
    return $databaseConnectionMock;
});
```
Function addOverrideRule returned a new instance of DIContainer which contains the override rule.
Now we can be confident that any instances of any object created by the new $diContainer instance will inject our mock of DatabaseConnection no matter how deeply nested the objects structure is.


#### What is boiler plate code?
Code that would do the work of a dependency injection container
This boiler plate code can be found in the performance tests
```php
function createClassZ() {
        return new Z(new Y(new X(new W(new V(new U(new T(new S(new R(new Q(new P(new O(new N(new M(new L(new K(new J(new I(new H(new G(new F(new E(new D(new C(new B(new A())))))))))))))))))))))))));
    }
```

## Tests
To run: vendor\bin\phpunit --bootstrap vendor\autoload.php tests\tests

# Performance test DiContainer vs Dice
To run: vendor\bin\phpunit --bootstrap vendor\autoload.php tests\performanceTests

Dice is super fast, doing some test against it seem like a good idea 
(https://github.com/Level-2/Dice)
PHP-DI is slow to start, but the more dependencys you have the better the performance (http://php-di.org/doc/php-definitions.html)

### A - Z tests
This test creates classes A - Z. Class B has a dependency on A, Class C has a dependency on B, all the way down to Z

Class | Dice | DIContainer | PHP-DI | Boiler plate
--- | --- | --- | --- | ---
A|0.29ms|0.26ms|2.18ms|0.16ms
B|0.96ms|0.73ms|3.61ms|0.27ms
C|1.67ms|1.12ms|3.22ms|0.36ms
D|2.23ms|1.53ms|3.17ms|0.48ms
E|2.87ms|1.96ms|3.21ms|0.57ms
F|3.65ms|2.79ms|3.37ms|0.76ms
G|4.43ms|2.72ms|3.23ms|0.77ms
H|4.76ms|3.13ms|3.22ms|0.9ms
I|5.64ms|4.35ms|3.26ms|1.08ms
J|6.16ms|4.12ms|3.31ms|1.12ms
K|6.81ms|4.39ms|4.27ms|1.85ms
L|9.59ms|5.6ms|3.25ms|1.39ms
M|10.2ms|5.46ms|4.58ms|1.58ms
N|8.74ms|7.06ms|4.28ms|1.55ms
O|11.58ms|11.05ms|6.75ms|2.5ms
P|9.96ms|6.58ms|3.23ms|1.89ms
Q|10.55ms|6.96ms|3.25ms|1.86ms
R|11.59ms|7.34ms|3.34ms|2.26ms
S|12.04ms|8.12ms|3.24ms|1.99ms
T|12.61ms|8.58ms|3.2ms|2.13ms
U|13.22ms|8.59ms|3.22ms|2.24ms
V|13.73ms|9.05ms|3.87ms|2.41ms
W|14.57ms|9.87ms|3.27ms|2.39ms
X|15.61ms|10.12ms|3.46ms|2.57ms
Y|16.42ms|11.1ms|3.18ms|2.7ms
Z|17.31ms|11.2ms|3.93ms|2.82ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.15ms
DiContainer|0.67ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|3.89ms
DiContainer|2.88ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|222.38ms
DiContainer|174.78ms
PHP DI|32.18ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|235.53ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|159.31ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|1.45ms
DiContainer|0.14ms