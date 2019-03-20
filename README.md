# DiContainer

Super simple dependency injection container for php

#### Getting started
To get an instance and all it's dependencies is as easy as this
```php
class ClassName {
    public function __constructor(A $a, B $b) {
    ...
    }
}

$objectName = DiContainer::loadInstanceOf(ClassName::class)
```

To make DiContainer return the same instance every time make sure your class implements SingleInstance
```php
class ClassName implements SingleInstance
{
...
}
```

To override SingleInstance from an extend class implement NewInstance 

#### Adding rules
You can add your rules directly to the resetOverrideRules
```php
    public function resetOverrideRules()
    {
        $this->overrideRules = [];
        $this->addOverrideRule( self::class, function () { return self::getInstance(); });
        //$this->addOverrideRule( MySingleton::class, function () { return MySingleton::getInstance(); });
    }
```
or you could call the instance and add the rules later

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
Dice|0.0078229904174805
DiContainer|0.0034599304199219
php7.3|-
Dice|0.0032179355621338
DiContainer|0.0030741691589355

### Create class J 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.21785092353821
DiContainer|0.12657999992371
php7.3|-
Dice|0.071502923965454
DiContainer|0.059846878051758

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.026506900787354
DiContainer|0.019238948822021
php7.3|-
Dice|0.018687963485718
DiContainer|0.010818004608154

### Create instance 3 level deep x2 each layer 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.11162400245667
DiContainer|0.079232931137085
php7.3|-
Dice|0.045221090316772
DiContainer|0.036789894104004


### Inject itself into class 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.024734973907471
DiContainer|0.0049588680267334
php7.3|-
Dice|0.010346174240112
DiContainer|0.0016400814056396
