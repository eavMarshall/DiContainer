# DiContainer
Super simple dependency injection container for PHP 7

#### How to start
```
$diContainer = new DIContainer();
$myClass = $diContainer->getInstanceOf(MyClass::class);
```

#### This container is designed to be
- Immutable
- Rule free
- Work immediately with no setup
- Help remove the singleton pattern by have share instances defined by implementing the SingleInstance interface
- Testing friendly, easily swapping instances for mock/stubs

##### Immutable?
In php you don't have a truly immutable object. There is some state held by the object. This state are the class names and keeping track of classes that implement the SingleInstance interface.
These values can never change while your application is running. This could be considered as immutable state.

##### Rule free?
Every container I've come across implements some sort of rule system. A rule system that needs to be loaded each and every time you run your application.
By labeling classes with an empty, but known interface, we can tag shared instances of classes without having to have a rule system.

##### Work immediately with no setup?
There are some containers that require some complex setup. Usually with json, xml or an array.
If you have an autoloader setup, a fully functioning container can be achieved without any setup.
```
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

##### Help remove the singleton pattern by have share instances defined by implementing the SingleInstance interface ?
Wrapping the singleton in a class that implements SingleInstance will allow you to request an instance directly from the container, or pass through a constructor of a instance instantiated via the container
In the example above, notice DatabaseConnection implements SingleInstance
```
$diContainer = new DIContainer();
$instance1 = $diContainer->getInstanceOf(DatabaseConnection::class);
$instance2 = $diContainer->getInstanceOf(DatabaseConnection::class);

assertSame($instance1, $instance2); //passes
```

##### Testing friendly, easily swapping instances for mock/stubs
If we wanted to stub out all DatabaseConnection to return a mock. Adding an override rule will replace all instances of your chosen class
```
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

## Tests
To run: vendor\bin\phpunit --bootstrap vendor\autoload.php tests\tests

# Performance test DiContainer vs Dice
To run: vendor\bin\phpunit --bootstrap vendor\autoload.php tests\performanceTests

Dice is super fast, doing some test against it seem like a good idea 
(https://github.com/Level-2/Dice)

### A - Z tests
This test creates classes A - Z. Class B has a dependency on A, Class C has a dependency on C, all the way down to Z

Class | Dice | DIContainer
--- | --- | ---
A|0.022691965103149|0.02939510345459
B|0.081801891326904|0.08405590057373
C|0.13765788078308|0.12718892097473
D|0.19683003425598|0.19118094444275
E|0.24084711074829|0.21868896484375
F|0.2906219959259|0.271075963974
G|0.34441590309143|0.32065486907959
H|0.39684295654297|0.3698878288269
I|0.45188999176025|0.42795491218567
J|0.50853800773621|0.46729183197021
K|0.55266094207764|0.50998997688293
L|0.62127590179443|0.57713103294373
M|0.65546202659607|0.60470104217529
N|0.72124195098877|0.64654994010925
O|0.7884259223938|0.6949610710144
P|0.82865810394287|0.75750207901001
Q|0.87227296829224|0.7867259979248
R|0.93593001365662|0.83533000946045
S|0.98573088645935|0.91264700889587
T|1.0602598190308|0.9466769695282
U|1.091826915741|0.97544097900391
V|1.1414630413055|1.0256388187408
W|1.2162899971008|1.0799210071564
X|1.2556359767914|1.1350028514862
Y|1.3215668201447|1.1830060482025
Z|1.4056069850922|1.2350149154663

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 100000 times
Container | Time
--- | ---
Dice|0.071977853775024
DiContainer|0.072658061981201

### Create instance 3 level deep x2 each layer 100000 times
Container | Time
--- | ---
Dice|0.34882283210754
DiContainer|0.35588312149048

### Inject itself into class 100000 times
Container | Time
--- | ---
Dice|0.065325975418091
DiContainer|0.0098321437835693
