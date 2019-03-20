<?php

use Di\DIContainer;
use Dice\Dice;
use tests\testClasses\ClassHoldingSessionInfoIsUpdated;
use tests\testClasses\ClassWithDiContainerDependency;
use tests\testClasses\nested\top;

final class performanceTests extends PHPUnit_Framework_TestCase
{
    const REPEAT = 10000;

    public function testDiceVsDiComponent()
    {
        $dice = new Dice();
        $containersToTest = ['dice' => $dice, 'diContainer' => DIContainer::getInstance()];

        $this->runTestOutput(
            'Create class A ' . self::REPEAT . ' times',
            $containersToTest,
            'A'
        );

        $this->runTestOutput(
            'Create class J ' . self::REPEAT . ' times',
            $containersToTest,
            'J'
        );

        $dice->addRule(Di\SessionInfo::class, ['shared' => true]);
        $this->runTestOutput(
            'Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated ' . self::REPEAT . ' times',
            $containersToTest,
            ClassHoldingSessionInfoIsUpdated::class
        );

        $this->runTestOutput(
            'Create instance 3 level deep x2 each layer ' . self::REPEAT . ' times',
            $containersToTest,
            top::class
        );

        printf("\n");
        printf('Inject itself into class ' . self::REPEAT . ' times');
        $dice->addRule(Dice::class, ['shared' => true]);
        $dice->create(Dice::class);
        $this->runTestOutput(
            null,
            ['dice' => $dice],
            DiceDependency::class
        );
        $this->runTestOutput(
            null,
            ['diContainer' => DIContainer::getInstance()],
            ClassWithDiContainerDependency::class
        );

        printf("\n\n");
    }

    private function runDiContainerTimer($container, $class)
    {
        $a = $container->getInstanceOf($class);
        $t1 = microtime(true);
        for ($i = 0; $i < self::REPEAT; $i++) {
            $a = $container->getInstanceOf($class);
        }
        $t2 = microtime(true);
        printf('DiContainer|' . ($t2 - $t1));
    }

    private function runDiceTimer($container, $class)
    {
        $a = $container->create($class);
        $t1 = microtime(true);
        for ($i = 0; $i < self::REPEAT; $i++) {
            $a = $container->create($class);
        }
        $t2 = microtime(true);
        printf('Dice|' . ($t2 - $t1));
    }

    private function runTestOutput($title, array $containers, $class)
    {
        if ($title !== null) {
            printf("\n");
            printf("### {$title}");
            printf("Container | Time\n");
            printf('--- | ---');
        }
        foreach ($containers as $name => $container) {
            printf("\n");
            if ($name === 'dice') {
                $this->runDiceTimer($container, $class);
            } else {
                $this->runDiContainerTimer($container, $class);
            }
        }
        if ($title !== null) {
            printf("\n");
        }
    }
}

class A {

}
class B {
    public $a;

    public function __construct(A $a) {
        $this->a = $a;
    }
}
class C {
    public $b;

    public function __construct(B $b) {
        $this->b = $b;
    }
}
class D {
    public $c;

    public function __construct(C $c) {
        $this->c = $c;
    }
}
class E {
    public $d;

    public function __construct(D $d) {
        $this->d = $d;
    }
}
class F {
    public $e;

    public function __construct(E $e) {
        $this->e = $e;
    }
}
class G {
    public $f;
    public function __construct(F $f) {
        $this->f = $f;
    }
}
class H {
    public $g;
    public function __construct(G $g) {
        $this->g = $g;
    }
}
class I {
    public $h;
    public function __construct(H $h) {
        $this->h = $h;
    }
}
class J {
    public $i;
    public function __construct(I $i) {
        $this->i = $i;
    }
}
class K
{
    public $j;

    public function __construct(K $k)
    {
        $this->k = $k;
    }
}

class DiceDependency
{
    public $dice;

    public function __construct(Dice $dice)
    {
        $this->dice = $dice;
    }
}
