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
Dice|0.0073890686035156
DiContainer|0.0036461353302002
php7.3|-
Dice|0.005648136138916
DiContainer|0.0040280818939209

### Create class J 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.19920897483826
DiContainer|0.12636804580688
php7.3|-
Dice|0.071223974227905
DiContainer|0.064021110534668

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.027147054672241
DiContainer|0.019090175628662
php7.3|-
Dice|0.010825872421265
DiContainer|0.0092861652374268

### Create instance 3 level deep x2 each layer 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.11182618141174
DiContainer|0.095668077468872
php7.3|-
Dice|0.041492223739624
DiContainer|0.04243803024292


### Inject itself into class 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.03341007232666
DiContainer|0.0069601535797119
php7.3|-
Dice|0.0085511207580566
DiContainer|0.00095200538635254