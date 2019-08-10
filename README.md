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

Class | Dice | DIContainer | Boiler plate
--- | --- | --- | ---
A|0.28ms|0.34ms|0.16ms
B|0.96ms|1.03ms|0.27ms
C|1.61ms|1.71ms|0.37ms
D|2.23ms|2.12ms|0.49ms
E|2.88ms|2.96ms|0.57ms
F|3.52ms|3.39ms|0.68ms
G|4.66ms|4.33ms|1.25ms
H|4.98ms|4.59ms|0.89ms
I|5.77ms|5.31ms|0.99ms
J|6.24ms|6.25ms|1.08ms
K|6.96ms|6.94ms|1.41ms
L|7.76ms|9.07ms|1.31ms
M|8.47ms|8.32ms|1.42ms
N|9.83ms|8.75ms|1.56ms
O|9.39ms|8.93ms|1.63ms
P|10.06ms|10.5ms|6.02ms
Q|20.14ms|18.22ms|1.82ms
R|11.58ms|11.3ms|2.27ms
S|12.55ms|13.06ms|3.73ms
T|21.19ms|20.49ms|3.33ms
U|23.79ms|22.09ms|3.52ms
V|22.79ms|13.64ms|2.5ms
W|14.81ms|13.4ms|2.43ms
X|15.99ms|14.54ms|2.59ms
Y|18.3ms|17.87ms|4.82ms
Z|26.1ms|16.09ms|2.91ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.55ms
DiContainer|0.84ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|4.45ms
DiContainer|4.67ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|308.55ms
DiContainer|221.05ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|234.36ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|222.52ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|1.04ms
DiContainer|0.14ms