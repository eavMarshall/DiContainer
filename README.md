# DiContainer
Super simple dependency injection container for PHP 5.6

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

### Create class A 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.0071628093719482
DiContainer|0.0036818981170654
php7.3|-
Dice|0.005648136138916
DiContainer|0.0040280818939209

### Create class J 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.21895003318787
DiContainer|0.12617683410645
php7.3|-
Dice|0.071223974227905
DiContainer|0.064021110534668

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.024976968765259
DiContainer|0.020330905914307
php7.3|-
Dice|0.010825872421265
DiContainer|0.0092861652374268

### Create instance 3 level deep x2 each layer 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.11167407035828
DiContainer|0.082062005996704
php7.3|-
Dice|0.041492223739624
DiContainer|0.04243803024292


### Inject itself into class 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.024260997772217
DiContainer|0.0051960945129395
php7.3|-
Dice|0.0085511207580566
DiContainer|0.00095200538635254
