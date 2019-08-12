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
A|0.3ms|0.29ms|2.25ms|0.16ms
B|0.99ms|1.02ms|3.42ms|0.3ms
C|1.74ms|1.2ms|3.4ms|0.37ms
D|2.36ms|1.59ms|3.33ms|0.48ms
E|2.87ms|2.01ms|3.52ms|0.61ms
F|3.63ms|2.93ms|3.34ms|0.67ms
G|4.65ms|2.94ms|3.77ms|0.76ms
H|13.71ms|3.6ms|4ms|1ms
I|5.65ms|3.81ms|3.69ms|1.11ms
J|6.43ms|8.64ms|12.66ms|4.11ms
K|21.58ms|7.64ms|3.4ms|1.17ms
L|8.83ms|9.63ms|4.59ms|2.05ms
M|29.76ms|9.9ms|5.12ms|2.17ms
N|15.41ms|9.39ms|5.78ms|2.41ms
O|15.41ms|10.53ms|5.79ms|2.16ms
P|18.44ms|18.55ms|8.64ms|1.75ms
Q|14.96ms|25.26ms|5.77ms|2.91ms
R|20.69ms|14.81ms|5.49ms|1.96ms
S|12.2ms|8.94ms|3.53ms|2.14ms
T|12.58ms|9.5ms|3.63ms|2.12ms
U|12.98ms|8.92ms|3.35ms|2.22ms
V|13.69ms|9.7ms|3.86ms|2.37ms
W|15.11ms|12.16ms|3.37ms|2.69ms
X|15.58ms|10.81ms|3.59ms|2.69ms
Y|15.64ms|11.85ms|3.53ms|2.74ms
Z|17.83ms|11.45ms|3.35ms|2.75ms

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 1000 times
Container | Time
--- | ---
Dice|1.07ms
DiContainer|0.7ms

### Create instance 3 level deep x2 each layer 1000 times
Container | Time
--- | ---
Dice|4.01ms
DiContainer|3.71ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|258.45ms
DiContainer|169.46ms
PHP DI|22.35ms

### Create AllClassesAToZDependenciesWithDice 1000 times
This class has a dependency on dice, a single instance and AllClassesAToZDependencies

Container | Time
--- | ---
Dice|241.51ms

### Create AllClassesAToZDependencies 1000 times
This class has a dependency on DIContainer, a single instance and AllClassesAToZDependenciesWithDiContainer

Container | Time
--- | ---
DiContainer|184.07ms

### Inject itself into class 1000 times
Container | Time
--- | ---
Dice|0.99ms
DiContainer|0.15ms