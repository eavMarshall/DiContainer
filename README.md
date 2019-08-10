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

### A - Z tests
This test creates classes A - Z. Class B has a dependency on A, Class C has a dependency on B, all the way down to Z

Class | Dice | DIContainer | PHP-DI | Boiler plate
--- | --- | --- | --- | ---
A|0.35ms|0.48ms|10.2ms|0.76ms
B|1.89ms|0.83ms|3.3ms|0.29ms
C|1.58ms|1.33ms|3.29ms|0.38ms
D|2.66ms|2.94ms|3.69ms|0.54ms
E|3.39ms|2.32ms|3.28ms|0.61ms
F|3.67ms|2.77ms|3.27ms|0.69ms
G|4.42ms|3.22ms|3.26ms|0.79ms
H|5.24ms|5.07ms|3.33ms|0.9ms
I|6.12ms|4.44ms|3.33ms|0.99ms
J|6.14ms|4.63ms|3.69ms|1.1ms
K|6.91ms|5.19ms|5.63ms|1.85ms
L|12.16ms|6.68ms|3.33ms|1.31ms
M|8.21ms|6.24ms|3.28ms|1.54ms
N|8.87ms|6.84ms|3.31ms|1.69ms
O|9.41ms|8.35ms|4.15ms|1.6ms
P|10.23ms|7.78ms|3.28ms|1.93ms
Q|10.76ms|8.21ms|3.27ms|1.81ms
R|11.58ms|8.72ms|3.28ms|1.9ms
S|11.99ms|9.25ms|3.55ms|2.02ms
T|12.65ms|9.63ms|3.25ms|2.12ms
U|13.42ms|10.45ms|3.26ms|2.19ms
V|13.93ms|10.61ms|3.27ms|2.27ms
W|14.47ms|11.04ms|3.45ms|2.4ms
X|15.69ms|11.49ms|3.8ms|2.53ms
Y|16.47ms|12.17ms|3.26ms|2.73ms
Z|17.39ms|12.66ms|3.38ms|2.9ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.15ms
DiContainer|0.75ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|4.07ms
DiContainer|3.51ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|225.08ms
DiContainer|210.08ms
PHP DI|23.45ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|269.64ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|191.43ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|1.83ms
DiContainer|0.15ms