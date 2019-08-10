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
A|0.49ms|0.54ms|2.84ms|0.16ms
B|1ms|0.83ms|3.27ms|0.29ms
C|1.72ms|2.72ms|4ms|0.61ms
D|3.21ms|1.95ms|5.47ms|0.53ms
E|3.42ms|2.78ms|3.84ms|0.63ms
F|6.56ms|4.01ms|3.98ms|0.74ms
G|4.84ms|4.22ms|3.75ms|0.8ms
H|8.74ms|4.59ms|4.33ms|0.9ms
I|7.41ms|4.81ms|3.82ms|1.03ms
J|6.26ms|4.69ms|3.4ms|1.11ms
K|7.65ms|6.42ms|4.16ms|1.2ms
L|8.08ms|7.73ms|5.49ms|2.02ms
M|12.62ms|9.94ms|5.35ms|2.19ms
N|14.57ms|9.98ms|3.34ms|1.57ms
O|10.79ms|7.4ms|3.35ms|1.97ms
P|11.12ms|7.73ms|3.33ms|1.7ms
Q|12.67ms|8.65ms|3.3ms|1.79ms
R|11.59ms|8.78ms|3.35ms|1.93ms
S|12.1ms|9.86ms|3.38ms|2.24ms
T|13.33ms|12.14ms|3.38ms|3.14ms
U|15.93ms|10.55ms|3.27ms|2.32ms
V|17.18ms|13.06ms|3.32ms|2.35ms
W|20.26ms|17.2ms|4.35ms|2.46ms
X|15.52ms|20.33ms|3.94ms|2.63ms
Y|19.7ms|12.08ms|3.84ms|3.01ms
Z|21.12ms|16.48ms|4.32ms|4.29ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|2ms
DiContainer|1.33ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|6ms
DiContainer|3.76ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|265.88ms
DiContainer|219.31ms
PHP DI|33.31ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|284.37ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|186.75ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|1.01ms
DiContainer|0.37ms