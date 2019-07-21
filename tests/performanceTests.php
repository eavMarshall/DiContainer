<?php

use Di\DIContainer;
use Di\SessionInfo;
use Dice\Dice;
use tests\testClasses\ClassHoldingSessionInfoIsUpdated;
use tests\testClasses\ClassWithDiContainerDependency;
use tests\testClasses\nested\top;
use PHPUnit\Framework\TestCase;

final class performanceTests extends TestCase
{
    const REPEAT = 100000;

    public function testDiceVsDiComponent()
    {
        $dice = new Dice();
        $diContainer = new DIContainer();

        printf("### A - Z tests\nThis test creates classes A - Z. Class B has a dependency on A, Class C has a" .
            " dependency on B, all the way down to Z");
        printf("\n\nClass | Dice | DIContainer\n");
        printf('--- | --- | ---');

        $letters = range('A', 'Z');
        $previousLetter =  null;
        foreach ($letters as $letter) {
            $this->runAToZTest(
                ['dice' => $dice, 'diContainer' => $diContainer],
                $letter
            );
        }
        printf("\n");

        $dice = new Dice();
        $diContainer = new DIContainer();
        $dice->addRule(Di\SessionInfo::class, ['shared' => true]);
        $containersToTest = ['dice' => $dice, 'diContainer' => $diContainer];
        $this->runTestOutput(
            'Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated ' . self::REPEAT . ' times',
            $containersToTest,
            ClassHoldingSessionInfoIsUpdated::class
        );

        $dice = new Dice();
        $diContainer = new DIContainer();
        $containersToTest = ['dice' => $dice, 'diContainer' => $diContainer];
        $this->runTestOutput(
            'Create instance 3 level deep x2 each layer ' . self::REPEAT . ' times',
            $containersToTest,
            top::class
        );

        $dice = new Dice();
        $diContainer = new DIContainer();
        $containersToTest = ['dice' => $dice, 'diContainer' => $diContainer];
        $this->runTestOutput(
            'Create AllClassesAToZDependencies ' . self::REPEAT . ' times'
            . "\nThis class has a dependency on all the A - Z classes\n",
            $containersToTest,
            'AllClassesAToZDependencies'
        );

        printf("\n");
        printf('### Inject itself into class ' . self::REPEAT . " times\n");
        printf("Container | Time\n");
        printf('--- | ---');
        $dice = new Dice();
        $diContainer = new DIContainer();
        $dice->addRule(Dice::class, ['shared' => true]);
        $dice->create(Dice::class);
        $this->runTestOutput(
            null,
            ['dice' => $dice],
            DiceDependency::class
        );
        $this->runTestOutput(
            null,
            ['diContainer' => $diContainer],
            ClassWithDiContainerDependency::class
        );

        printf("\n\n");

        self::assertTrue(1 == true);
    }

    private function runDiContainerTimer($container, $class)
    {
        printf('DiContainer|' . $this->runDiContainerTimerNoPrint($container, $class));
    }

    private function runDiceTimer($container, $class)
    {
        printf('Dice|' . $this->runDiceTimerNoPrint($container, $class));
    }

    private function runTestOutput($title, array $containers, $class)
    {
        if ($title !== null) {
            printf("\n");
            printf("### {$title}");
            printf("\nContainer | Time\n");
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

    private function runDiContainerTimerNoPrint($container, $class)
    {
        $a = $container->getInstanceOf($class);
        $t1 = microtime(true);
        for ($i = 0; $i < self::REPEAT; $i++) {
            $a = $container->getInstanceOf($class);
        }
        $t2 = microtime(true);
        return $t2 - $t1;
    }

    private function runDiceTimerNoPrint($container, $class)
    {
        $a = $container->create($class);
        $t1 = microtime(true);
        for ($i = 0; $i < self::REPEAT; $i++) {
            $a = $container->create($class);
        }
        $t2 = microtime(true);
        return $t2 - $t1;
    }

    private function runAToZTest(array $containers, $class)
    {
        printf("\n{$class}");
        foreach ($containers as $name => $container) {
            if ($name === 'dice') {
                printf("|{$this->runDiceTimerNoPrint($container, $class)}");
            } else {
                printf("|{$this->runDiContainerTimerNoPrint($container, $class)}");
            }
        }
    }
}

class A {

}

$letters = range('A', 'Z');
$previousLetter =  null;
foreach ($letters as $letter) {
    if ($previousLetter === null) {
        $previousLetter = $letter;
        continue;
    }
    eval("
        class {$letter}
        { 
            public \$parent;

            public function __construct({$previousLetter} \$parent) {
                \$this->parent = \$parent;
            }
        }
    ");

    $previousLetter = $letter;
}

$level1ConstructorArguments = '';
$level1Fields = '';
$level1Assignments = '';

foreach ($letters as $letter) {
    $level1ConstructorArguments .= "{$letter} \${$letter}, ";
    $level1Fields .= "\${$letter}, ";
    $level1Assignments .= "\$this->{$letter} = \${$letter}; ";
}

$level1ConstructorArguments = substr($level1ConstructorArguments, 0, -2);
$level1Fields = substr($level1Fields, 0, -2);

eval("
    class AllClassesAToZDependencies
    { 
        private {$level1Fields};
        public function __construct({$level1ConstructorArguments}) {
            {$level1Assignments}
        }
    }
");

class DiceDependency
{
    public $dice;

    public function __construct(Dice $dice)
    {
        $this->dice = $dice;
    }
}
