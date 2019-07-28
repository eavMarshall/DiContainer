<?php
/**
 * @author     Elliott Marshall
 * @copyright  2019
 * @license    MIT
 * @version    1.0
 */

namespace Di;

use Exception;
use ReflectionClass;

class DIContainer
{
    private static $thisInstance;
    private $overrideRules = [];
    private $implementsNewInstanceCache = [];
    private $singeInstancesCache = [];
    private $constructorCache = [];

    public function __construct($overrideRules = [])
    {
        $that = $this;
        $this->overrideRules[self::class] = static function () use ($that) {
            return $that;
        };
        unset($overrideRules[self::class]);
        $this->overrideRules = array_merge($this->overrideRules, $overrideRules);
    }

    public static function getInstance()
    {
        return self::$thisInstance ?: self::$thisInstance = new self();
    }

    /**
     * Put here so developers don't have to call DIContainer::getInstance()->getInstanceOf(<<namespace class name>>)
     * @param $class
     * @return mixed
     * @throws Exception
     */
    public static function loadInstanceOf($class)
    {
        return self::getInstance()->getInstanceOf($class);
    }

    /**
     * This function is only here to help testing.
     * DiContainer rules are immutable. This is to prevent someone from adding a rule in production code. When a
     * rule is added, a new instance of DIContainer is returned with the new rule.
     * In production a containers should only ever had 1 rule, return itself if an instance of itself is required
     *
     * @param $class
     * @param $overrideFunction
     * @return DIContainer
     */
    public function addOverrideRule($class, $overrideFunction)
    {
        $newRule = [];
        $newRule[$class] = $overrideFunction;
        $newRule = array_merge($this->overrideRules, $newRule);
        return new self($newRule);
    }

    public function getInstanceOf($class, array $parameters = null)
    {
        if ($class === null) {
            return null;
        }

        if (isset($this->overrideRules[$class])) {
            return $this->overrideRules[$class]();
        }

        if ($parameters === null) {
            if (isset($this->singeInstancesCache[$class])) {
                return $this->singeInstancesCache[$class];
            }

            $instance = new $class(...$this->getParams($class));

            if ($this->implementsNewInstanceCache[$class] !== false) {
                $this->singeInstancesCache[$class] = $instance;
            }

            return $instance;
        }

        return new $class(...$parameters);
    }

    private function getParams($classPath)
    {
        if (!isset($this->constructorCache[$classPath])) {
            return $this->getParamOnFirstRun($classPath);
        }

        if (!$this->constructorCache[$classPath]) {
            return [];
        }

        $paramInstances = [];
        foreach ($this->constructorCache[$classPath] as $type) {
            $paramInstances[] = $this->getParamInstance($type);
        }

        return $paramInstances;
    }

    private function getParamOnFirstRun($classPath)
    {
        $reflectionClass = new ReflectionClass($classPath);
        $constructor = $reflectionClass->getConstructor();

        $this->implementsNewInstanceCache[$classPath] = !$reflectionClass->implementsInterface(NewInstance::class)
            && $reflectionClass->implementsInterface(SingleInstance::class);

        if ($constructor === null) {
            return $this->constructorCache[$classPath] = [];
        }

        $parameters = $constructor->getParameters();
        $this->constructorCache[$classPath] = [];
        $initialParamInstances = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            $initialParamInstances[] = $this->getParamInstance($this->constructorCache[$classPath][] = $type ? $type->getName() : null);
        }

        return $initialParamInstances;
    }

    private function getParamInstance($type)
    {
        return $this->getOverrideRules($type) ?: $this->getInstanceOf($type);
    }

    private function getOverrideRules($type)
    {
        return isset($this->overrideRules[$type]) ? $this->overrideRules[$type]() : [];
    }
}