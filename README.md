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
Dice|0.0071628093719482
DiContainer|0.0036818981170654
php7.3|-
Dice|0.0051369667053223
DiContainer|0.0067892074584961

### Create class J 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.21895003318787
DiContainer|0.12617683410645
php7.3|-
Dice|0.1142840385437
DiContainer|0.052088975906372

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.024976968765259
DiContainer|0.020330905914307
php7.3|-
Dice|0.010689973831177
DiContainer|0.0079789161682129

### Create instance 3 level deep x2 each layer 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.11167407035828
DiContainer|0.082062005996704
php7.3|-
Dice|0.042171955108643
DiContainer|0.040060043334961


### Inject itself into class 10000 times
Container | Time
--- | ---
php5.6|-
Dice|0.024260997772217
DiContainer|0.0051960945129395
php7.3|-
Dice|0.010339021682739
DiContainer|0.001223087310791