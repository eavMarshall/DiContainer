<?php

namespace Di;

use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use function array_merge;

/**
 * @author     Elliott Marshall
 * @copyright  2022
 * @license    MIT
 */
class DIContainer
{
    private static $instance;
    private array $overrideRules;
    private array $singeInstancesCache = [];
    private array $constructorCache = [];

    public function __construct($overrideRules = [])
    {
        $this->overrideRules = array_merge($overrideRules, [self::class => function () { return $this; }]);
    }

    public static function getInstance()
    {
        return self::$instance ?: self::$instance = new self();
    }

    #[Pure] public function addOverrideRule($class, $overrideFunction)
    {
        return new self(array_merge($this->overrideRules, [$class => $overrideFunction]));
    }

    public function getInstanceOf($class)
    {
        if (!$class) return null;
        if (isset($this->overrideRules[$class])) return $this->overrideRules[$class]();

        return $this->singeInstancesCache[$class] ?? $this->buildInstance($class);
    }

    /** @throws ReflectionException */
    private function buildInstance($class)
    {
        if (!isset($this->constructorCache[$class])) return $this->buildInstanceOnFirstRun($class);

        $paramInstances = [];
        foreach ($this->constructorCache[$class] as $type) {
            $paramInstances[] = $this->getInstanceOf($type);
        }

        return new $class(...$paramInstances);
    }

    /** @throws ReflectionException */
    private function buildInstanceOnFirstRun($class)
    {
        $reflectionClass = new ReflectionClass($class);
        if ($reflectionClass->isInterface()) return null;

        $this->constructorCache[$class] = [];
        $initialParamInstances = [];

        $parameters = $reflectionClass?->getConstructor()?->getParameters() ?? [];
        foreach ($parameters as $parameter) {
            $typeName = !$parameter->getType()?->isBuiltin() ? $parameter->getType()?->getName() : null;
            $initialParamInstances[] = $this->getInstanceOf($this->constructorCache[$class][] = $typeName);
        }

        return $reflectionClass->implementsInterface(SingleInstance::class)
            ? $this->singeInstancesCache[$class] = new $class(...$initialParamInstances)
            : new $class(...$initialParamInstances);
    }
}
