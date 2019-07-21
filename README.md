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
This test creates classes A - Z. Class B has a dependency on A, Class C has a dependency on B, all the way down to Z

Class | Dice | DIContainer
--- | --- | ---
A|0.044289112091064|0.042750120162964
B|0.10041403770447|0.12282204627991
C|0.17916893959045|0.17083883285522
D|0.31793117523193|0.25261807441711
E|0.30870008468628|0.33421611785889
F|0.42656183242798|0.47712397575378
G|0.5031590461731|0.54069995880127
H|0.54921579360962|0.66392493247986
I|0.65836501121521|0.60846400260925
J|0.68578720092773|0.67256999015808
K|0.7875669002533|0.86429595947266
L|0.84890699386597|0.84253787994385
M|0.96507692337036|0.98111581802368
N|0.97194695472717|1.0832509994507
O|1.0355582237244|1.0185220241547
P|1.2008919715881|1.1531550884247
Q|1.5146260261536|1.6249480247498
R|1.5083479881287|1.4917008876801
S|1.377368927002|1.4957458972931
T|1.5540552139282|1.416934967041
U|1.4882838726044|1.4600028991699
V|1.6365699768066|1.5198199748993
W|1.6227700710297|1.7654569149017
X|1.6776328086853|1.7138221263885
Y|1.8897731304169|1.7995500564575
Z|1.8878500461578|1.741837978363

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 100000 times
Container | Time
--- | ---
Dice|0.11007595062256
DiContainer|0.090126037597656

### Create instance 3 level deep x2 each layer 100000 times
Container | Time
--- | ---
Dice|0.43825888633728
DiContainer|0.47243690490723

### Create AllClassesAToZDependencies 100000 times
This class has a dependency on all the A - Z classes

Container | Time
--- | ---
Dice|24.008610010147
DiContainer|24.062613964081

### Inject itself into class 100000 times
Container | Time
--- | ---
Dice|0.10549187660217
DiContainer|0.013836145401001