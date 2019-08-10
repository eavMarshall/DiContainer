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
A|0.29ms|0.36ms|2.99ms|0.19ms
B|1.26ms|0.97ms|3.34ms|0.27ms
C|1.61ms|1.6ms|3.39ms|0.41ms
D|2.38ms|2.22ms|3.32ms|0.51ms
E|5.72ms|2.79ms|3.35ms|0.61ms
F|3.57ms|3.42ms|3.52ms|0.7ms
G|4.22ms|4ms|3.7ms|1.27ms
H|5.12ms|4.46ms|3.35ms|0.91ms
I|7.2ms|8.6ms|3.53ms|1.03ms
J|6.19ms|5.84ms|3.47ms|1.13ms
K|7.21ms|6.56ms|3.35ms|1.27ms
L|7.98ms|7.36ms|3.47ms|1.33ms
M|8.12ms|7.64ms|3.36ms|1.4ms
N|8.78ms|8.45ms|3.44ms|1.51ms
O|9.63ms|9.16ms|3.39ms|1.61ms
P|10.38ms|9.49ms|3.41ms|1.68ms
Q|10.8ms|10.11ms|3.39ms|1.81ms
R|12.34ms|10.74ms|3.45ms|1.98ms
S|12.43ms|11.51ms|3.42ms|2.08ms
T|13.2ms|12.64ms|3.37ms|2.16ms
U|16.26ms|14.36ms|5.97ms|3.57ms
V|16.42ms|13.28ms|3.32ms|2.35ms
W|14.65ms|13.7ms|3.36ms|2.45ms
X|16.19ms|15.29ms|3.35ms|2.56ms
Y|16.52ms|15.36ms|3.31ms|2.71ms
Z|16.62ms|15.51ms|3.38ms|2.76ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.05ms
DiContainer|1.3ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|3.97ms
DiContainer|4.18ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|232.6ms
DiContainer|244.76ms
PHP DI|24.48ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|282.05ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|221.8ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|0.99ms
DiContainer|0.15ms