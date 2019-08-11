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
A|0.29ms|0.34ms|3.38ms|0.17ms
B|0.97ms|0.83ms|3.24ms|0.28ms
C|2.1ms|1.31ms|3.74ms|0.37ms
D|2.29ms|1.76ms|3.25ms|0.49ms
E|3.42ms|3.06ms|6.91ms|0.59ms
F|3.74ms|2.77ms|3.3ms|0.68ms
G|4.21ms|3.66ms|3.26ms|0.76ms
H|4.93ms|3.63ms|3.25ms|0.87ms
I|5.61ms|4.12ms|3.26ms|1ms
J|6.42ms|4.6ms|4.23ms|1.5ms
K|6.78ms|5.07ms|3.34ms|1.17ms
L|7.61ms|5.61ms|3.27ms|1.27ms
M|8.05ms|6.03ms|3.69ms|1.45ms
N|8.67ms|6.64ms|4.64ms|1.51ms
O|9.79ms|7.32ms|5.48ms|2.15ms
P|17.59ms|13.31ms|5.77ms|2.64ms
Q|18.63ms|14.6ms|5.41ms|2.8ms
R|20.14ms|15.01ms|4.52ms|2.97ms
S|21.46ms|15.88ms|5.43ms|3.1ms
T|22.34ms|16.2ms|5ms|2.95ms
U|23.02ms|10.06ms|4.74ms|3.5ms
V|25.24ms|17.32ms|5.69ms|3.63ms
W|26.81ms|18.32ms|3.52ms|3.77ms
X|27.4ms|19.44ms|5.21ms|3.43ms
Y|28.05ms|21.98ms|5.69ms|3.57ms
Z|29.5ms|21.3ms|4.81ms|4.26ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.64ms
DiContainer|1.31ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|6.51ms
DiContainer|5.73ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|367.05ms
DiContainer|339.44ms
PHP DI|58.62ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|303.85ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|174.13ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|0.97ms
DiContainer|0.14ms