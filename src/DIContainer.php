<?php
/**
 * @author     Elliott Marshall
 * @copyright  2019
 * @license    MIT
 * @version    1.0
 */

namespace Di;

use ReflectionClass;

class DIContainer
{
    private static $thisInstance;
    private $overrideRules = [];
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

    /**
     * Instead of creating a new instance of DIContainer, you should probably use this function get get your instance
     * in your application code.
     * This will help keep your single instances collection unique.
     * In test code however, you should try and create a new DIContainer every test, to prevent your single instance
     * state from spilling into another test
     * @return DIContainer
     */
    public static function getInstance()
    {
        return self::$thisInstance ?: self::$thisInstance = new self();
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

    /**
     * Function getInstanceOf creates a new instance of $class that do not implements SingleInstance interface
     * Classes that implement single instance interface will have the same instance returned
     * @param $class
     * @param $parameters
     * @return mixed|null
     */
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

            $instance = $this->_getInstance($class);

            return $instance;
        }

        return new $class(...$parameters);
    }

    private function _getInstance($class)
    {
        if (isset($this->constructorCache[$class])) {
            if (empty($this->constructorCache[$class])) {
                return new $class();
            }

            $paramInstances = [];
            foreach ($this->constructorCache[$class] as $type) {
                $paramInstances[] = $this->getInstanceOf($type);
            }

            return new $class(...$paramInstances);
        }

        return $this->_getInstanceOnFirstRun($class);
    }

    private function _getInstanceOnFirstRun($class)
    {
        $reflectionClass = new ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();
        $this->constructorCache[$class] = [];
        $initialParamInstances = [];

        if ($constructor !== null) {
            $parameters = $constructor->getParameters();

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                $initialParamInstances[] = $this->getInstanceOf($this->constructorCache[$class][] = $type ? $type->getName() : null);
            }
        }

        $instance = new $class(...$initialParamInstances);

        if ($reflectionClass->implementsInterface(SingleInstance::class)) {
            $this->singeInstancesCache[$class] = $instance;
        }

        return $instance;
    }
}