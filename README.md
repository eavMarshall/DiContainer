[![Build Status](https://img.shields.io/travis/eavMarshall/DiContainer/master.svg?style=flat-square)](https://travis-ci.org/eavMarshall/DiContainer)
[![Coverage Status](https://coveralls.io/repos/github/eavMarshall/DiContainer/badge.svg?branch=master)](https://coveralls.io/github/eavMarshall/DiContainer?branch=master)

# DiContainer
DiContainer is a lightweight dependency injection container for PHP 7.1 - 7.3

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

##PHP 7.3

### A - Z tests
This test creates classes A - Z. Class B has a dependency on A, Class C has a dependency on B, all the way down to Z

Class | Dice | DIContainer | PHP-DI | Boiler plate
--- | --- | --- | --- | ---
A|0.32ms|0.27ms|2.21ms|0.16ms
B|0.94ms|0.73ms|3.72ms|0.44ms
C|1.67ms|1.15ms|3.71ms|0.44ms
D|2.31ms|1.55ms|5.73ms|1.05ms
E|6.2ms|2ms|3.37ms|0.59ms
F|3.63ms|2.49ms|3.42ms|1.02ms
G|4.85ms|2.85ms|3.4ms|0.77ms
H|4.87ms|3.24ms|3.34ms|0.9ms
I|5.58ms|3.71ms|3.86ms|1.01ms
J|7.76ms|4.1ms|3.35ms|1.09ms
K|6.78ms|4.48ms|3.3ms|1.2ms
L|7.5ms|4.86ms|3.33ms|1.28ms
M|8.15ms|5.37ms|3.31ms|1.37ms
N|8.76ms|5.74ms|3.31ms|1.49ms
O|9.35ms|6.13ms|3.39ms|1.58ms
P|10.01ms|6.58ms|3.33ms|1.67ms
Q|10.72ms|7.17ms|3.38ms|1.77ms
R|11.31ms|7.56ms|3.33ms|1.9ms
S|13.28ms|8ms|3.41ms|2.03ms
T|12.98ms|8.41ms|3.65ms|2.25ms
U|15.5ms|8.75ms|3.32ms|2.26ms
V|14.44ms|9.01ms|3.32ms|2.3ms
W|17.04ms|9.61ms|3.38ms|2.52ms
X|16.39ms|10.34ms|3.48ms|2.86ms
Y|16.7ms|10.91ms|3.48ms|2.87ms
Z|17.57ms|11.02ms|4.55ms|2.71ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.18ms
DiContainer|0.71ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|4.06ms
DiContainer|3.06ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|232.75ms
DiContainer|151.44ms
PHP DI|22.47ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|238.37ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|152.79ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|1.33ms
DiContainer|0.13ms

##PHP 5.6 

### A - Z tests
This test creates classes A - Z. Class B has a dependency on A, Class C has a dependency on B, all the way down to Z

Class | Dice | DIContainer | PHP-DI | Boiler plate
--- | --- | --- | --- | ---
A|0.71ms|0.66ms|11.91ms|0.26ms
B|2.36ms|1.6ms|28.22ms|0.52ms
C|4.21ms|2.56ms|28ms|0.83ms
D|5.91ms|3.54ms|27.83ms|1.11ms
E|7.61ms|4.54ms|27.85ms|1.41ms
F|9.25ms|5.47ms|28.09ms|1.69ms
G|11.02ms|6.47ms|28.22ms|1.97ms
H|14.96ms|11.34ms|32.04ms|2.35ms
I|15.05ms|8.95ms|29.74ms|2.68ms
J|17.39ms|10.06ms|29.46ms|3.17ms
K|21.74ms|11.99ms|30.74ms|3.23ms
L|20.63ms|12.68ms|31.13ms|3.58ms
M|22.74ms|13.6ms|30.5ms|3.87ms
N|24.17ms|14.13ms|29.48ms|4.08ms
O|25.79ms|14.97ms|29.26ms|4.52ms
P|27.89ms|16.59ms|29.68ms|5.03ms
Q|29.89ms|17.26ms|29.47ms|5.28ms
R|30.01ms|17.5ms|27.96ms|5.39ms
S|31.65ms|18.32ms|28.02ms|5.69ms
T|33.21ms|19.22ms|27.93ms|5.92ms
U|35.06ms|20.72ms|28.21ms|6.34ms
V|36.76ms|21.26ms|27.86ms|6.65ms
W|38.71ms|22.21ms|27.91ms|6.96ms
X|40.67ms|23.35ms|28.24ms|7.35ms
Y|42.76ms|24.62ms|27.99ms|7.7ms
Z|44.47ms|25.31ms|27.89ms|7.98ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|2.59ms
DiContainer|1.47ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|10.25ms
DiContainer|7.67ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|595.47ms
DiContainer|345.75ms
PHP DI|216.16ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|683.76ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|354.46ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|2.1ms
DiContainer|0.31ms
